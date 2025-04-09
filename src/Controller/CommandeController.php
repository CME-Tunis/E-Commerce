<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Panier;
use App\Repository\PanierRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository,TokenStorageInterface $tokenStorage,
    AuthorizationCheckerInterface $authChecker): Response
    {
        $token = $tokenStorage->getToken();
    
        if (!$token || !$token->getUser()) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }
    
        if ($authChecker->isGranted('ROLE_ADMIN')) {
            // L'utilisateur a le rôle ROLE_ADMIN
        }
    
        $user = $token->getUser();
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
            'user' => $user,
        ]);
    }
    #[Route('/valider', name: 'valider_commande', methods: ['GET', 'POST'])]
    public function validerCommande(
        Security $security,
        EntityManagerInterface $entityManager,
        PanierRepository $panierRepository
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté'], 401);
        }
    
        // Récupérer tous les produits du panier de l'utilisateur
        $paniers = $panierRepository->findBy(['panierUser' => $user]);
    
        if (!$paniers) {
            return new JsonResponse(['success' => false, 'message' => 'Votre panier est vide.'], 400);
        }
    
        // Création d'une nouvelle commande
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDate(new \DateTime()); // Date actuelle
        $commande->setStatusCommande(StatusCommande::EN_COURS); // Enum du statut de commande
        $commande->setQuatiteReserve(array_sum(array_map(fn($p) => $p->getQuantite(), $paniers)));
    
        // Calcul du prix total de la commande
        $prixTotal = array_sum(array_map(fn($p) => $p->getPrixTotale(), $paniers));
        $commande->setPrix($prixTotal);
    
        // Associer les paniers à la commande et vider le panier
        foreach ($paniers as $panier) {
            $panier->setPanierCommande($commande);
            $entityManager->persist($panier);
            $entityManager->remove($panier); // Supprimer le panier après l'association
        }
    
        // Sauvegarde de la commande
        $entityManager->persist($commande);
        $entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'message' => 'Commande créée avec succès et panier vidé.',
            'commande_id' => $commande->getId(),
            'prixTotal' => $commande->getPrix(),
            'quantiteTotale' => $commande->getQuatiteReserve(),
        ]);
    }
    
    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

   
    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
