<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistreController extends AbstractController
{
    #[Route('/registre', name: 'app_registre')]
    public function RegisterUser(ManagerRegistry $managerRegistry, Request $request): Response
    {
        
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $entityManager = $managerRegistry ->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->redirect('app_login');
        }
        
        return $this->renderForm('registre/index.html.twig', [
            'form' => $form
        ]);
        //return $this->render('registre/index.html.twig', [
       //     'controller_name' => 'RegistreController',
        //]);
    }
}
