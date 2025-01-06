<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;

class ProduitdetailController extends AbstractController
{
    #[Route('/produitdetail/{id}', name: 'app_produitdetail')]
    public function produitDetail($id): Response
    {
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id);
    
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
    
        return $this->render('Produitdetail/produit.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/detail', name: 'app_detail')]
    public function detail(): Response
    {
        // Simple affichage de la vue sans données spécifiques
        return $this->render('boutique/Product Detail.html.twig');
    }
   

}
