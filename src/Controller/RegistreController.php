<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistreController extends AbstractController
{

    private $userPasswordEncoderInterface;
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->userPasswordEncoderInterface=$userPasswordEncoderInterface;
    }
    #[Route('/registre', name: 'app_registre')]
    public function RegisterUser(ManagerRegistry $managerRegistry, Request $request): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_task');
        }
        
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
           
            $password_hashed=$this->userPasswordEncoderInterface->encodePassword($user,$user->getPassword());
            $user->setPassword($password_hashed);
            $user =$form->getData(); 
            $entityManager = $managerRegistry ->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_login');
        }
        
        return $this->renderForm('registre/index.html.twig', [
            'form' => $form 
        ]);
        //return $this->render('registre/index.html.twig', [
       //     'controller_name' => 'RegistreController',
        //]);
    }
}
