<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditProfilFormType;
use App\Form\ImageProfileFormType;
use App\Form\ResetPasswordFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    private $userPasswordEncoderInterface;
   
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, UserPasswordEncoderInterface $passwordEncoder,UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager,ManagerRegistry $managerRegistry): Response
    {
        $user1 = $this->getUser();
        if (!$user1 instanceof UserInterface) {
            throw new \LogicException('This can not occur');
        }
        $user1=  $userRepository->find($user1->getId());
        $form1 = $this->createForm(ResetPasswordFormType::class,$user1);
        $form1->handleRequest($request);

       

        if($form1->isSubmitted() && $form1->isValid()){

            // Crypter le nouveau mot de passe
        $newPassword = $form1->get('password')->getData();
        $encodedPassword = $passwordEncoder->encodePassword($user1, $newPassword);
        
        // Mettre à jour le mot de passe de l'utilisateur
        $user1->setPassword($encodedPassword);
            
            $entityManager->persist($user1);
            $entityManager->flush();

            $this->addFlash(
                'Success',
                'Mot de passe changer avec succés !'
            );

            return $this->redirectToRoute('app_task');

        }
        $user = $this->getUser();
      
        $form = $this->createForm(EditProfilFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'Success',
                'Profile modifie avec succés !'
            );
            return $this->redirectToRoute('app_task');
            
                
        }
        $user2 = $this->getUser();
        if (!$user2 instanceof UserInterface) {
            throw new \LogicException('This can not occur');
        }
        $user2=  $userRepository->find($user1->getId());
        $formImage = $this->createForm(ImageProfileFormType::class,$user2);
        $formImage->handleRequest($request);
        if ($formImage->isSubmitted() && $formImage->isValid()) {
            $file = $formImage->get('imageUrl')->getData();
            if ($file) {
                // Generate a unique name for the file
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
    
                // Move the file to the desired directory
                $file->move(
                    $this->getParameter('image_directory'), // Path to the directory where images will be saved
                    $fileName
                );
    
                // Update the user's profile picture property with the file name
                $user2->setImageUrl($fileName); // Assuming you have a setProfilePicture method in your User entity
    
            }
    
            $entityManager->persist($user2);
            $entityManager->flush();
            $this->addFlash(
                'Success',
                'Profile modifie image avec succés !'
            );
            return $this->redirectToRoute('app_task');
            
                
        }


      
            return $this->render('profile/front.html.twig', [
                'form' => $form->createView(),
                'passForm' => $form1->createView(),
                'modifImage' => $formImage->createView()
            ]);
        
        
    }
    #[Route('/profileadmin', name: 'app_profileadmin')]
    public function profileadmin(Request $request, UserPasswordEncoderInterface $passwordEncoder,UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager,ManagerRegistry $managerRegistry): Response
    {
        $user1 = $this->getUser();
        if (!$user1 instanceof UserInterface) {
            throw new \LogicException('This can not occur');
        }
        $user1=  $userRepository->find($user1->getId());
        $form1 = $this->createForm(ResetPasswordFormType::class,$user1);
        $form1->handleRequest($request);

       

        if($form1->isSubmitted() && $form1->isValid()){

            // Crypter le nouveau mot de passe
        $newPassword = $form1->get('password')->getData();
        $encodedPassword = $passwordEncoder->encodePassword($user1, $newPassword);
        
        // Mettre à jour le mot de passe de l'utilisateur
        $user1->setPassword($encodedPassword);
            
            $entityManager->persist($user1);
            $entityManager->flush();

            $this->addFlash(
                'Success',
                'Mot de passe changer avec succés !'
            );

            return $this->redirectToRoute('app_task');

        }
        $user = $this->getUser();
      
        $form = $this->createForm(EditProfilFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'Success',
                'Profile modifie avec succés !'
            );
            return $this->redirectToRoute('app_task');
            
                
        }
        $user2 = $this->getUser();
        if (!$user2 instanceof UserInterface) {
            throw new \LogicException('This can not occur');
        }
        $user2=  $userRepository->find($user1->getId());
        $formImage = $this->createForm(ImageProfileFormType::class,$user2);
        $formImage->handleRequest($request);
        if ($formImage->isSubmitted() && $formImage->isValid()) {
            $file = $formImage->get('imageUrl')->getData();
            if ($file) {
                // Generate a unique name for the file
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
    
                // Move the file to the desired directory
                $file->move(
                    $this->getParameter('image_directory'), // Path to the directory where images will be saved
                    $fileName
                );
    
                // Update the user's profile picture property with the file name
                $user2->setImageUrl($fileName); // Assuming you have a setProfilePicture method in your User entity
    
            }
    
            $entityManager->persist($user2);
            $entityManager->flush();
            $this->addFlash(
                'Success',
                'Profile modifie image avec succés !'
            );
            return $this->redirectToRoute('app_task');
            
                
        }


        
            return $this->render('profile/back.html.twig', [
                'form' => $form->createView(),
                'passForm' => $form1->createView(),
                'modifImage' => $formImage->createView()
            ]);
        
        
    }
}
