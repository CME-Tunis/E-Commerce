<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/user/{id}', name: 'get_or_create_panier', methods: ['GET'])]
    public function getOrCreatePanier(User $user, EntityManagerInterface $em): JsonResponse
    {
        // Vérifier si l'utilisateur a déjà un panier
        $panier = $em->getRepository(Panier::class)->findOneBy(['panierUser' => $user]);

        if (!$panier) {
            // Si l'utilisateur n'a pas encore de panier, on en crée un
            $panier = new Panier();
            $panier->setPanierUser($user);
            $em->persist($panier);
            $em->flush();
        }

        return $this->json([
            'message' => 'Panier récupéré/créé avec succès',
            'panier_id' => $panier->getId(),
        ]);
    }
    #[Route('/mon-panier', name: 'mon_panier', methods: ['GET'])]
public function voirMonPanier(PanierRepository $panierRepository): JsonResponse
{
    $user = $this->getUser(); // Récupérer l'utilisateur connecté

    if (!$user) {
        return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
    }

    // Récupérer les produits du panier de l'utilisateur
    $paniers = $panierRepository->findBy(['panierUser' => $user]);

    // Transformer les résultats en tableau JSON
    $panierData = [];

    foreach ($paniers as $panier) {
        $produit = $panier->getPanierProd();
        $panierData[] = [
            'id' => $produit->getId(),
            'nom' => $produit->getNomP(),
            'quantite' => $panier->getQuantite(),
            'prixUnitaire' => $produit->getPrix(),
            'prixTotal' => $panier->getPrixTotale(),
        ];
    }

    return new JsonResponse([
        'success' => true,
        'panier' => $panierData,
    ]);
}


    #[Route('/ajouter/{id}', name: 'ajouter_panier', methods: ['GET', 'POST'])]
    public function ajouterAuPanier(
        Produit $produit, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        PanierRepository $panierRepository
    ): JsonResponse {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
    
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }
    
        $data = json_decode($request->getContent(), true);
        $quantite = $data['quantite'] ?? 1;
    
        if ($quantite < 1) {
            return new JsonResponse(['success' => false, 'message' => 'Quantité invalide'], 400);
        }
    
        // Vérifier si ce produit est déjà dans le panier de l'utilisateur
        $panier = $panierRepository->findOneBy([
            'panierProd' => $produit,
            'panierUser' => $user,
        ]);
    
        if ($panier) {
            // Si le produit est déjà dans le panier, mettre à jour la quantité
            $ancienneQuantite = $panier->getQuantite();
            $panier->setQuantite($ancienneQuantite + $quantite);
        } else {
            // Sinon, créer un nouvel objet Panier pour cet utilisateur et ce produit
            $panier = new Panier();
            $panier->setPanierUser($user);
            $panier->setPanierProd($produit);
            $panier->setQuantite($quantite);
        }
    
        // Calcul du prix total
        $panier->setPrixTotale($panier->getQuantite() * $produit->getPrix());
    
        // Sauvegarde des modifications
        $entityManager->persist($panier);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'message' => 'Produit ajouté au panier avec succès',
            'produit' => [
                'id' => $produit->getId(),
                'nom' => $produit->getNomP(),
                'quantite' => $panier->getQuantite(),
                'prixTotale' => $panier->getPrixTotale(),
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

    // // Optionnel : une fonction pour valider la commande à partir du panier
    // #[Route('/valider-commande', name: 'valider_commande')]
    // public function validerCommande(): Response
    // {
    //     $user = $this->security->getUser();

    //     if (!$user) {
    //         return $this->redirectToRoute('app_login'); // Rediriger vers la page de login
    //     }

    //     // Récupérer le panier actif de l'utilisateur
    //     $panier = $this->entityManager->getRepository(Panier::class)->findOneBy([
    //         'user' => $user,
    //         'panierCommande' => null, // Vérifier si ce panier n'est pas encore associé à une commande
    //     ]);

    //     if (!$panier) {
    //         // Si aucun panier trouvé, rediriger l'utilisateur
    //         return $this->redirectToRoute('associer_panier');
    //     }

    //     // Créer une commande pour l'utilisateur
    //     $commande = new Commande();
    //     $commande->setUser($user);
    //     $commande->setDate(new \DateTime());
    //     $commande->setStatusCommande('EN_COURS');
    //     $this->entityManager->persist($commande);

    //     // Associer le panier à la commande
    //     $panier->setPanierCommande($commande);
    //     $this->entityManager->flush();

    //     return $this->redirectToRoute('commande_details', ['id' => $commande->getId()]);
    // }

    // #[Route('/contenu', name: 'panier_contenu')]

    // public function getPanierContent(SessionInterface $session)
    // {
    //     $panier = $session->get('panier', []);
    //     $total = 0;
    //     $items = [];
    
    //     foreach ($panier as $id => $quantite) {
    //         $produit = $this->getDoctrine()->getRepository(Produit::class)->find($id);
    //         if ($produit) {
    //             $total += $produit->getPrix() * $quantite;
    //             $items[] = [
    //                 'id' => $produit->getId(),
    //                 'nomP' => $produit->getNomP(),
    //                 'image' => $produit->getImage(),
    //                 'quantite' => $quantite,
    //                 'prix' => $produit->getPrix(),
    //             ];
    //         }
    //     }
    
    //     return $this->json([
    //         'paniers' => $items,
    //         'sommeTotale' => $total
    //     ]);
    // }
    
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
