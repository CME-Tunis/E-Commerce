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

class ShopingCartController extends AbstractController
{
    #[Route('/shoping', name: 'app_shoping_cart')]
    public function index( ProduitRepository $produitRepository, CategoryRepository $categorieRepository, PanierRepository $panierRepository): Response
    {
         // Récupérer tous les paniers
         $paniers = $panierRepository->findAll();

         // Calculer la somme totale
         $sommeTotale = array_reduce($paniers, function ($total, $panier) {
             return $total + $panier->getPrixTotale();
         }, 0);
        return $this->render('boutique/Shoping Cart.html.twig', [
            'controller_name' => 'ShopingCartController',
            'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
            'categories' => $categorieRepository->findAll(),
            'paniers' => $panierRepository->findAll(),
            'sommeTotale' => $sommeTotale, // Liste des catégories
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

        // Calcul des ajustements
        $ancienneQuantite = $panier->getQuantite();
        $ajustementStock = $ancienneQuantite - $nouvelleQuantite;

        // Vérifier si le stock est suffisant
        if ($produit->getStock() + $ajustementStock < 0) {
            return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant'], 400);
        }

        // Mettre à jour le panier et le stock du produit
        $panier->setQuantite($nouvelleQuantite);
        $panier->setPrixTotale($produit->getPrix() * $nouvelleQuantite);
        $produit->setStock($produit->getStock() + $ajustementStock);

        // Sauvegarder les modifications
        $entityManager->persist($panier);
        $entityManager->persist($produit);
        $entityManager->flush();

        // Recalculer la somme totale du panier
        $sommeTotale = $this->recalculerSommeTotale($panierRepository);

        return new JsonResponse([
            'success' => true,
            'newTotalPrice' => $panier->getPrixTotale(),
            'newStock' => $produit->getStock(),
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


    #[Route('/shoping-cart', name: 'shoping_cart', methods: ['GET'])]
    public function showCart(PanierRepository $panierRepository): Response
    {
        // Récupérer tous les paniers
        $paniers = $panierRepository->findAll();
    
        // Calculer la somme totale des prix
        $sommeTotale = array_reduce($paniers, function ($carry, $panier) {
            return $carry + $panier->getPrixTotale();
        }, 0);
    
        return $this->render('shoping_cart/index.html.twig', [
            'paniers' => $paniers,
            'sommeTotale' => $sommeTotale,
        ]);
    }
     

}
