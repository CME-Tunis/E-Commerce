<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use App\Repository\PanierRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
class ProduitdetailController extends AbstractController
{
    #[Route('/produitdetail/{id}', name: 'app_produitdetail')]
    
        public function produitDetail( int $id, ProduitRepository $produitRepository ,   Security $security, PanierRepository $panierRepository, CategoryRepository $categorieRepository,  Request $request): Response {
            $user = $security->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $quantitePanier = 0;

        if ($user) {
            // Récupérer la quantité totale des produits dans le panier de l'utilisateur connecté
            $quantitePanier = $panierRepository->getQuantiteTotaleParUtilisateur($user);
        }
    // Gestion AJAX pour mise à jour dynamique
    if ($request->isXmlHttpRequest()) {
        return $this->json([
            'quantitePanier' => $quantitePanier
        ]);
    }
        // Si l'ID est présent, on récupère le produit, sinon on ne fait pas de recherche
        if ($id) {
            $produit = $produitRepository->find($id);
    
            if (!$produit) {
                throw $this->createNotFoundException('Produit non trouvé.');
            }
        } else {
            $produit = null;  // Ou gérer le cas autrement si besoin
        }
    
        $paniers = $panierRepository->findBy(['panierUser' => $user]);
        $sommeTotale = $panierRepository->getTotalPanierByUser($user);
    
            // Rendre la vue avec les détails du produit, tous les produits et les catégories
            return $this->render('boutique/Product Detail.html.twig', [
                'produit' => $produit,
                'paniers' => $paniers,
                   'sommeTotale' => $sommeTotale,
                'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
                'categories' => $categorieRepository->findAll(), // Liste des catégories
                'quantitePanier' => $quantitePanier,
            ]);
        }
    
 
    #[Route('/detail', name: 'app_detail')]
    public function detail(): Response
    {
        // Simple affichage de la vue sans données spécifiques
        return $this->render('boutique/Product Detail.html.twig', [
            'controller_name' => 'ProduitdetailController',
            
        ]);
    }
   

}
