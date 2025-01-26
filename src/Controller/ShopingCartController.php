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
        return $this->render('boutique/Shoping Cart.html.twig', [
            'controller_name' => 'ShopingCartController',
            'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
            'categories' => $categorieRepository->findAll(),
            'paniers' => $panierRepository->findAll(), // Liste des catégories
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
        // Décoder les données envoyées par le client
        $data = json_decode($request->getContent(), true);
        $quantite = $data['quantite'] ?? null;
    
        // Vérifier si le panier existe
        $panier = $panierRepository->find($id);
        if (!$panier) {
            return new JsonResponse(['success' => false, 'message' => 'Panier non trouvé'], 404);
        }
    
        // Récupérer le produit lié au panier
        $produit = $panier->getPanierProd();
        if (!$produit) {
            return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
    
        // Vérifier si la quantité est valide et ne dépasse pas le stock disponible
        $stockDisponible = (int) $produit->getStock();
        if ($quantite === null || $quantite < 1 || $quantite > $stockDisponible) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Quantité invalide ou supérieure au stock disponible',
            ], 400);
        }
    
        // Mettre à jour la quantité et recalculer le prix total
        $panier->setQuantite($quantite);
        $panier->setPrixTotale($produit->getPrix() * $quantite);
    
        // Sauvegarder les modifications
        $entityManager->persist($panier);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'newTotalPrice' => $panier->getPrixTotale(),
        ]);}
}
