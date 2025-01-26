<?php

namespace App\Controller;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoutiqueController extends AbstractController
{
    #[Route('/boutique', name: 'app_boutique')]
    public function index(ProduitRepository $produitRepository, CategoryRepository $categorieRepository): Response
    {
        return $this->render('boutique/index.html.twig', [
            'controller_name' => 'BoutiqueController',
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }
 
}
