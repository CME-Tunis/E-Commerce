<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/ajouter/{id}', name: 'ajouter_panier', methods: ['GET','POST'])]
    public function ajouterAuPanier(
        Produit $produit, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        PanierRepository $panierRepository,
        CommandeRepository $commandeRepository,
        Security $security
    ): JsonResponse {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 403);
        }
    
        // Récupérer la commande en cours de l'utilisateur
        $commande = $commandeRepository->findOneBy(['user' => $user, 'status' => 'en_cours']);
    
        if (!$commande) {
            return new JsonResponse(['success' => false, 'message' => 'Aucune commande en cours'], 404);
        }
    
        // Décoder les données JSON
        $data = json_decode($request->getContent(), true);
        $quantite = $data['quantite'] ?? 1;
    
        // Vérifier si la quantité est valide
        if ($quantite < 1) {
            return new JsonResponse(['success' => false, 'message' => 'Quantité invalide'], 400);
        }
    
        // Vérifier le stock disponible
        if ($produit->getStock() < $quantite) {
            return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant'], 400);
        }
    
        // Vérifier si le produit est déjà dans le panier de cette commande
        $panier = $panierRepository->findOneBy(['panierProd' => $produit, 'panierCommande' => $commande]);
    
        if ($panier) {
            // Mise à jour de la quantité
            $nouvelleQuantite = $panier->getQuantite() + $quantite;
    
            // Vérifier le stock avant mise à jour
            if ($produit->getStock() < $nouvelleQuantite) {
                return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant'], 400);
            }
    
            $panier->setQuantite($nouvelleQuantite);
        } else {
            // Création d'un nouveau panier pour cette commande
            $panier = new Panier();
            $panier->setPanierProd($produit);
            $panier->setPanierCommande($commande); // Associer au bon panier de la commande
            $panier->setQuantite($quantite);
        }
    
        // Mise à jour du prix total du panier
        $panier->setPrixTotale($panier->getQuantite() * $produit->getPrix());
    
        // Mise à jour du stock du produit
        $produit->setStock($produit->getStock() - $quantite);
    
        // Sauvegarder les modifications
        $entityManager->persist($panier);
        $entityManager->persist($produit);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'message' => 'Produit ajouté au panier avec succès',
            'produit' => [
                'id' => $produit->getId(),
                'nom' => $produit->getNomP(),
                'quantite' => $panier->getQuantite(),
                'prixTotale' => $panier->getPrixTotale(),
                'stockRestant' => $produit->getStock(),
            ],
        ]);
    }

    #[Route('/update', name: 'update_panier', methods: ['POST'])]
    public function updatePanier(Request $request, EntityManagerInterface $em, PanierRepository $panierRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $produitId = $data['produitId'];
    $nouvelleQuantite = (int)$data['quantite'];

    if ($nouvelleQuantite < 1) {
        return new JsonResponse(['error' => 'Quantité invalide.'], 400);
    }

    $panier = $panierRepository->findOneBy(['produit' => $produitId]);
    if (!$panier) {
        return new JsonResponse(['error' => 'Produit introuvable dans le panier.'], 404);
    }

    $panier->setQuantite($nouvelleQuantite);
    $panier->setPrixTotale($panier->getProduit()->getPrix() * $nouvelleQuantite);

    $em->persist($panier);
    $em->flush();

    return new JsonResponse([
        'prixTotale' => $panier->getPrixTotale(),
        'quantite' => $panier->getQuantite(),
    ]);
    }
    
    #[Route('/', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
       // Récupérer tous les paniers
    $paniers = $panierRepository->findAll();

    // Calculer le prix total dynamiquement pour chaque élément du panier
    foreach ($paniers as $panier) {
        if ($panier->getPanierProd() !== null && $panier->getQuantite() !== null) {
            $prixUnitaire = $panier->getPanierProd()->getPrix();
            $quantite = $panier->getQuantite();
            $panier->setPrixTotale($prixUnitaire * $quantite); // Appliquer la formule
        }
    }

    // Rendre la vue
    return $this->render('panier/index.html.twig', [
        'paniers' => $paniers,
    ]);
    }

    #[Route('/new', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($panier);
            $entityManager->flush();

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('panier/new.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($panier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
