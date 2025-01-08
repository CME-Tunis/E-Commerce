<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Repository\CategoryRepository;

class ShopingCartController extends AbstractController
{
    #[Route('/shoping', name: 'app_shoping_cart')]
    public function index( ProduitRepository $produitRepository, CategoryRepository $categorieRepository): Response
    {
        return $this->render('boutique/Shoping Cart.html.twig', [
            'controller_name' => 'ShopingCartController',
            'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
            'categories' => $categorieRepository->findAll(), // Liste des catégories
        ]);
    }
}
