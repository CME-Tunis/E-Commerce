<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Repository\CategoryRepository;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(ProduitRepository $produitRepository, CategoryRepository $categorieRepository): Response
    {
        return $this->render('contact/contact.html.twig', [
            'controller_name' => 'ContactController',
            'produits' => $produitRepository->findAll(), // Pour afficher d'autres produits
            'categories' => $categorieRepository->findAll(), // Liste des catégories
        ]);
    }
}
