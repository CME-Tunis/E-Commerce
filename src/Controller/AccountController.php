<?php

namespace App\Controller;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(ProduitRepository $produitRepository, CategoryRepository $categorieRepository): Response
    {
        return $this->render('account/account.html.twig', [
            'controller_name' => 'AccountController',
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll(), 
        ]);
    }

}
