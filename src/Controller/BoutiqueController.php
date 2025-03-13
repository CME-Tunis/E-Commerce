<?php

namespace App\Controller;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Panier;
use App\Repository\PanierRepository;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(ProduitRepository $produitRepository, CategoryRepository $categorieRepository,  PanierRepository $panierRepository
    ,   Security $security,): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        // Récupérer uniquement le panier de l'utilisateur connecté
        $paniers = $panierRepository->findBy(['panierUser' => $user]);
        $quantitePanier = 0;

        if ($user) {
            // Récupérer la quantité totale des produits dans le panier de l'utilisateur connecté
            $quantitePanier = $panierRepository->getQuantiteTotaleParUtilisateur($user);
        }
        // Calculer le montant total uniquement pour cet utilisateur
        $sommeTotale = $panierRepository->getTotalPanierByUser($user);
        return $this->render('boutique/index.html.twig', [
          
            'produits' => $produitRepository->findAll(),
            'sommeTotale' => $sommeTotale,
            'paniers' => $paniers,
            'categories' => $categorieRepository->findAll(),
            'quantitePanier' => $quantitePanier,
        ]);
    }
    #[Route('/boutique/add/{id}', name: 'add_to_cart', methods: ['GET','POST'])]
    public function addToCart(
        int $id,
        EntityManagerInterface $entityManager,
        Security $security,
        PanierRepository $panierRepository,
        ProduitRepository $produitRepository
    ): JsonResponse {
        // Vérifier si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 403);
        }
    
        // Récupérer le produit
        $produit = $produitRepository->find($id);
        if (!$produit) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
    
        // Vérification du stock disponible
        if ($produit->getStock() < 1) {
            return $this->json(['success' => false, 'message' => 'Produit en rupture de stock'], 400);
        }
    
        // Vérifier si le produit est déjà dans le panier
        $panier = $panierRepository->findOneBy(['panierUser' => $user, 'panierProd' => $produit]);
    
        if ($panier) {
            // Si le produit existe déjà dans le panier, on incrémente la quantité
            $nouvelleQuantite = $panier->getQuantite() + 1;
    
            // Vérification du stock avant d'incrémenter
            if ($nouvelleQuantite > $produit->getStock()) {
                return $this->json(['success' => false, 'message' => 'Quantité demandée supérieure au stock disponible'], 400);
            }
    
            $panier->setQuantite($nouvelleQuantite);
            $panier->setPrixTotale($produit->getPrix() * $panier->getQuantite());
        } else {
            // Sinon, on l'ajoute au panier
            $panier = new Panier();
            $panier->setPanierUser($user);
            $panier->setPanierProd($produit);
            $panier->setQuantite(1);
            $panier->setPrixTotale($produit->getPrix());
            $entityManager->persist($panier);
        }
    
        $entityManager->flush();
    
        // Recalculer le total du panier
        $sommeTotale = $panierRepository->getTotalPanierByUser($user);
    
        return $this->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'panierId' => $panier->getId(),
            'quantite' => $panier->getQuantite(),
            'newTotalPrice' => number_format($panier->getPrixTotale(), 3, '.', ''),
            'sommeTotale' => number_format($sommeTotale, 3, '.', ''),
            'productName' => $produit->getNomP(),
            'productPrice' => number_format($produit->getPrix(), 3, '.', '')
        ]);
    }
    

    #[Route('/boutique/cart-contents', name: 'cart_contents', methods: ['GET'])]
    public function getCartContents(
        Security $security,
        PanierRepository $panierRepository
    ): JsonResponse {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 403);
        }
    
        // Récupérer le panier pour cet utilisateur
        $paniers = $panierRepository->findBy(['panierUser' => $user]);
    
        $result = [];
        $sommeTotale = 0;
        foreach ($paniers as $panier) {
            $produit = $panier->getPanierProd();
            $quantite = $panier->getQuantite();
            $prixTotale = $panier->getPrixTotale();
            $sommeTotale += $prixTotale;
    
            $result[] = [
                'id' => $panier->getId(),
                'quantite' => $quantite,
                'prixTotale' => number_format($prixTotale, 3, '.', ''),
                'panierProd' => [
                    'nomP' => $produit->getNomP(),
                    'image' => $produit->getImage()
                ]
            ];
        }
    
        return $this->json([
            'success' => true,
            'paniers' => $result,
            'sommeTotale' => number_format($sommeTotale, 3, '.', '')
        ]);
    } 
    
    #[Route('/boutique/update/{id}', name: 'update_boutique_cart', methods: ['GET', 'POST'])]
    public function updateBoutiqueCartItem(
        int $id,
        Request $request,
        PanierRepository $panierRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $nouvelleQuantite = $data['quantite'] ?? null;
    
        if (!is_numeric($nouvelleQuantite) || $nouvelleQuantite < 1) {
            return new JsonResponse(['success' => false, 'message' => 'Quantité invalide'], 400);
        }
    
        $panier = $panierRepository->find($id);
        if (!$panier) {
            return new JsonResponse(['success' => false, 'message' => 'Panier non trouvé'], 404);
        }
    
        $produit = $panier->getPanierProd();
        if (!$produit) {
            return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
    
        $panier->setQuantite($nouvelleQuantite);
        $panier->setPrixTotale($produit->getPrix() * $nouvelleQuantite);
    
        $entityManager->persist($panier);
        $entityManager->flush();
    
        $sommeTotale = $panierRepository->getTotalPanierByUser($panier->getPanierUser());
    
        return new JsonResponse([
            'success' => true,
            'newTotalPrice' => number_format($panier->getPrixTotale(), 3, '.', ''),
            'sommeTotale' => number_format($sommeTotale, 3, '.', '')
        ]);
    }
    
    #[Route('/boutique/remove/{id}', name: 'remove_from_boutique_cart', methods: ['GET', 'POST'])]
    public function removeFromBoutiqueCart($id, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();
    
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non connecté'], 403);
        }
    
        $panierRepo = $entityManager->getRepository(Panier::class);
        $panier = $panierRepo->find($id);
    
        if (!$panier) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
    
        $entityManager->remove($panier);
        $entityManager->flush();
    
        $paniersRestants = $panierRepo->findBy(['panierUser' => $user]);
        $nouveauTotal = array_reduce($paniersRestants, fn($carry, $panier) => $carry + $panier->getPrixTotale(), 0);
    
        return $this->json([
            'success' => true,
            'sommeTotale' => number_format($nouveauTotal, 3, '.', '')
        ]);
    }

}
