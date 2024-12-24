<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/front', name: 'app_front')]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
            $produits = $produitRepository->findAll(),
        ]);
    }
    #[Route('/front', name: 'app_front')]
    public function afficheProduit(): Response
    {
        $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();
        return $this->render('front/index.html.twig', [
            'produits' => $produits,
        ]);
    }
    #[Route('/produit/{id}', name: 'front_produit_detail', methods: ['GET'])]
    public function produitDetail($id, ProduitRepository $produitRepository): Response
    {
        // Récupérer le produit par ID
        $produit = $produitRepository->find($id);

        // Vérifier si le produit existe
        if (!$produit) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas.');
        }

        // Retourner une vue pour afficher le produit
        return $this->render('front/produit_detail.html.twig', [
            'produit' => $produit,
        ]);
    }
}
