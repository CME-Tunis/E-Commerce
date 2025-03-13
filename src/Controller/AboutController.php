<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Repository\CategoryRepository;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function index(ProduitRepository $produitRepository, CategoryRepository $categorieRepository): Response
    {
        return $this->render('about/about.html.twig', [
            'controller_name' => 'AboutController',
            'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
                'categories' => $categorieRepository->findAll(), // Liste des catégories
        ]); 
    }
} 
