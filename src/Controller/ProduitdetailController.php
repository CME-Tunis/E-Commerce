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

class ProduitdetailController extends AbstractController
{
    #[Route('/produitdetail/{id}', name: 'app_produitdetail')]
    
        public function produitDetail( int $id, ProduitRepository $produitRepository ,   Security $security, PanierRepository $panierRepository, CategoryRepository $categorieRepository): Response {
            // Récupérer le produit par son ID
             $produit = $produitRepository->find($id);
             $user = $security->getUser();
             if (!$user) {
                 return $this->redirectToRoute('app_login');
             }
         
            // Vérification si le produit existe
            if (!$produit) {
                throw $this->createNotFoundException('Produit non trouvé.');
            }
            $paniers = $panierRepository->findBy(['panierUser' => $user]);
              // Calculer le montant total uniquement pour cet utilisateur
        $sommeTotale = $panierRepository->getTotalPanierByUser($user);
            // Rendre la vue avec les détails du produit, tous les produits et les catégories
            return $this->render('boutique/Product Detail.html.twig', [
                'produit' => $produit,
                'paniers' => $paniers,
                   'sommeTotale' => $sommeTotale,
                'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
                'categories' => $categorieRepository->findAll(), // Liste des catégories
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
