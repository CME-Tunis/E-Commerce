<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Panier;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use App\Repository\PanierRepository;
use Symfony\Component\HttpFoundation\Request;
class AccountController extends AbstractController
{
    #[Route('/account/{id?}', name: 'app_account')]
    public function index(
        ProduitRepository $produitRepository,
        Security $security,
        ?int $id,  // ID optionnel
        PanierRepository $panierRepository,
        CategoryRepository $categorieRepository,
        Request $request
    ): Response {
        $user = $security->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
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
    
        return $this->render('account/account.html.twig', [
            'produit' => $produit,
            'paniers' => $paniers,
            'sommeTotale' => $sommeTotale,
            'controller_name' => 'AccountController',
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
   
        ]);
    }
     
}
