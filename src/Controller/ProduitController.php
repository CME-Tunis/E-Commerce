<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository ,TokenStorageInterface $tokenStorage,
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
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
            'user' => $user,
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,TokenStorageInterface $tokenStorage,
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
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }
                $produit->setImage($newFilename);
            }
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit ,TokenStorageInterface $tokenStorage,
    AuthorizationCheckerInterface $authChecker): Response
    { $token = $tokenStorage->getToken();
    
        if (!$token || !$token->getUser()) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }
    
        if ($authChecker->isGranted('ROLE_ADMIN')) {
            // L'utilisateur a le rôle ROLE_ADMIN
        }
        $user = $token->getUser();
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager ,TokenStorageInterface $tokenStorage,
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
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }
                $produit->setImage($newFilename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
