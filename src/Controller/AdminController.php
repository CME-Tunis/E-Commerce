<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker
    ): Response {
        $token = $tokenStorage->getToken();
    
        if (!$token || !$token->getUser()) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }
    
        if ($authChecker->isGranted('ROLE_ADMIN')) {
            // L'utilisateur a le rôle ROLE_ADMIN
        }
    
        $user = $token->getUser();
    
        return $this->render('admin/admin.html.twig', [
            'user' => $user,
        ]);
    }
}
