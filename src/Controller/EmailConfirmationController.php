<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EmailConfirmationController extends AbstractController
{
    #[Route('/email/confirmation/{token}', name: 'app_email_confirmation')]
    public function index($token, UserRepository $repo, EntityManagerInterface $em): Response
    {
        // 'token' est le nom du champ dans la base de données
        $user = $repo->findOneBy(['token' => $token]);
        if(!$user) {

            throw $this->createNotFoundException('Ce token de confirmation est invalide');
        }
        $user->setToken(null);
        $user->setIsVerified(true);

        $em->persist($user);
        $em->flush();
        return $this->render('email_confirmation/index.html.twig', [
            'user' => $user
        ]);
    }
}
