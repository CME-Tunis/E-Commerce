<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;

class ProduitdetailController extends AbstractController
{
    #[Route('/produitdetail/{id}', name: 'app_produitdetail', requirements: ['id' => '\d+'])]
    public function index(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        // Si aucun produit n'est trouvé, on retourne une page 404
        if (!$produit) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas.');
        }

        // Rendu de la vue avec le produit trouvé
        return $this->render('boutique/Product Detail.html.twig', [
            'controller_name' => 'ProduitdetailController',
            'produit' => $produit,
        ]);
    }
    
    

}
