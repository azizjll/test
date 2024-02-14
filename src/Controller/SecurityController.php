<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

       // Vérifie si un utilisateur est déjà connecté
    if ($this->getUser()) {
        // Vérifie si le compte de l'utilisateur est vérifié
        if (!$this->getUser()->getIsVerified()) {
            $this->addFlash(
                'danger',
                'Le compte n\'est pas vérifié ! Veuillez vérifier votre boîte de réception !'
            );
        // Vérifie si le compte de l'utilisateur est bloqué
        } elseif (!$this->getUser()->getEtat()) {
            $this->addFlash(
                'danger',
                'Votre compte est bloqué !'
            );
        } else {
            // Si le compte est vérifié et actif, redirige en fonction du rôle de l'utilisateur
            if (in_array('ROLE_CLIENT', $this->getUser()->getRoles())) {
                $this->addFlash(
                    'success',
                    'Connexion réussie en tant que client !'
                );
                return $this->redirectToRoute('app_task');
            } elseif (in_array('ROLE_COACH', $this->getUser()->getRoles())) {
                $this->addFlash(
                    'success',
                    'Connexion réussie en tant que coach !'
                );
                return $this->redirectToRoute('app_back');
            }
        }
    }

    // Si aucun utilisateur n'est connecté, affichez la page de connexion normalement
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();
    return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
            /*
            if (in_array('ROLE_CLIENT', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('app_task');
            } elseif (in_array('ROLE_COACH', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('app_back');
            }
            
            
             
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);*/
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout():Response
    {
       throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
       //return $this->redirectToRoute('app_task');
    }
}
