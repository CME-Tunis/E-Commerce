<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitRepository;
use App\Repository\PanierRepository;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Repository\CategoryRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Repository\UserRepository;
class ShopingCartController extends AbstractController
{
    #[Route('/shoping', name: 'app_shoping_cart')]
    public function index(
        ProduitRepository $produitRepository,
        Security $security,
        CategoryRepository $categorieRepository,
        PanierRepository $panierRepository
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $quantitePanier = 0;

        if ($user) {
            // Récupérer la quantité totale des produits dans le panier de l'utilisateur connecté
            $quantitePanier = $panierRepository->getQuantiteTotaleParUtilisateur($user);
        }
        // Récupérer uniquement le panier de l'utilisateur connecté
        $paniers = $panierRepository->findBy(['panierUser' => $user]);
    
        // Calculer le montant total uniquement pour cet utilisateur
        $sommeTotale = $panierRepository->getTotalPanierByUser($user);
    
        return $this->render('boutique/Shoping Cart.html.twig', [
            'paniers' => $paniers,
            'sommeTotale' => $sommeTotale,
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
            'quantitePanier' => $quantitePanier,
        ]);
    }
    
    #[Route('/shoping-cart/update/{id}', name: 'update_shoping_cart', methods: ['GET', 'POST'])]
    public function updateCartItem(
        Request $request,
        PanierRepository $panierRepository,
        ProduitRepository $produitRepository,
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Décoder les données envoyées
        $data = json_decode($request->getContent(), true);
        $nouvelleQuantite = $data['quantite'] ?? null;
    
        // Vérifier si la nouvelle quantité est valide
        if (!is_numeric($nouvelleQuantite) || $nouvelleQuantite < 1) {
            return new JsonResponse(['success' => false, 'message' => 'Quantité invalide'], 400);
        }
    
        // Vérifier si le panier existe
        $panier = $panierRepository->find($id);
        if (!$panier) {
            return new JsonResponse(['success' => false, 'message' => 'Panier non trouvé'], 404);
        }
    
        // Vérifier si le produit lié au panier existe
        $produit = $panier->getPanierProd();
        if (!$produit) {
            return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
    
        // Mettre à jour la quantité du panier sans toucher au stock du produit
        $panier->setQuantite($nouvelleQuantite);
        $panier->setPrixTotale($produit->getPrix() * $nouvelleQuantite);
    
        // Sauvegarder les modifications
        $entityManager->persist($panier);
        $entityManager->flush();
    
        // Recalculer la somme totale du panier
        $sommeTotale = $panierRepository->getTotalPanierByUser($panier->getPanierUser());
    
        return new JsonResponse([
            'success' => true,
            'newTotalPrice' => $panier->getPrixTotale(),
            'sommeTotale' => $sommeTotale,
        ]);
    }

    private function recalculerSommeTotale(PanierRepository $panierRepository): float
    {
        return $panierRepository->createQueryBuilder('p')
            ->select('SUM(p.prixTotale)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0.0;
    }
    #[Route('/shoping-cart/remove/{id}', name: 'remove_from_cart', methods: ['POST'])]
    public function removeFromCart($id, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        $user = $security->getUser();
    
        // Vérifier si l'utilisateur est connecté
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
    
        // Recalculer le total du panier
        $paniersRestants = $panierRepo->findBy(['panierUser' => $user]);
        $nouveauTotal = array_reduce($paniersRestants, function ($carry, $panier) {
            return $carry + $panier->getPrixTotale();
        }, 0);
    
        return $this->json([
            'success' => true,
            'sommeTotale' => $nouveauTotal
        ]);
    }
    
    
    
}
