<?php

namespace App\Controller;

use App\Service\EmailService;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route ('/forgot-password', name:'app_forgot_password')]
    public function forgotPassword(UserRepository $repo, Request $request, EmailService $emailService, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em): Response
    {
        $form = $this->createForm((ForgotPasswordType::class));
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            //recupération l'email saisie par l'utilisateur dans le formulaire
            $email = $form->get('email')->getData();
            //Cherche dans la bdd le mail de l'utilisateur connecté
            $user = $repo->findOneBy(['email'=> $email]);
            if(!$user) {
                throw $this->createNotFoundException("L'adresse mail n'existe pas");
            }
            //génération d'un token
            $token = Uuid::v4()->toRfc4122();
            $user->setToken($token);
            $em->persist($user);
            $em->flush();
            //génération du lien
            $link = $urlGenerator->generate(
                'app_home', //changer pour 'app_reset_password' et le créer
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $emailService->sendEmail(
                'sullivan.espeut@gmail.com',
                $email,
                'Reinitialisation du mot de passe',
                'Cliquez sur le lien: <a href="'.$link.'" >Cliquez ici</a>'
            );
        }
        return $this->render('login/forgot_password.html.twig', [
         'form' => $form
            
        ]);
    }
    // #[Route('/reset-password', name:"app_reset-password")]
    // public function resetPassword() 
    // {

    // }
}
