<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
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
    public function RegisterUser(ManagerRegistry $managerRegistry, Request $request,SendMailService $mail, JWTService  $jwt ): Response
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

            // On génere le JWT de l'utilisateur 
            // On crée le header
            $header = [
                'typ'=> 'JWT',
                'alg' => 'HS256'
            ];

            // On crée le payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token 
            $token = $jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));
            
            //sendemail
            $mail->send(
                'no-reply@monsite.com',
                $user->getEmail(),
                'Activation de votre compte',
                'register',
                compact('user','token')
                
            );

            $this->addFlash(
                'Success',
                'Registration successfully !'
            );
            $this->addFlash(
                'Warning',
                ' Veuillez activer votre compte tout d\'abord ! !'
            );
            
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('registre/index.html.twig', [
            'form' => $form->createView(),
        ]);
        //return $this->render('registre/index.html.twig', [
       //     'controller_name' => 'RegistreController',
        //]);
    }
    #[Route('/verif/{token}',name:'verify_user')]
    public function verifyUser($token, JWTService $jwt,UserRepository $userRepository,EntityManagerInterface $entityManagerInterface):Response
    {
        //dd($this->jwt->isValid($token));
        //dd($this->jwt->getPayload($token));
        //dd($this->jwt->isExpired($token));
        //dd($jwt->check($token,$this->getParameter('app.jwtsecret')));
        
        

        //On verifie si le token est valide , n'a pas expiré et n'a pas ete modifier
        if($jwt->isValid($token) && !$jwt->isExpired($token)&& $jwt->check($token,$this->getParameter('app.jwtsecret')))
        {
            //On récupère le payloas
            $payload = $jwt->getPayload($token);
            //On récupère le user du token
            $user = $userRepository->find($payload['user_id']);

            // On verifier que l'utisateur existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $entityManagerInterface->flush($user);

                $this->addFlash(
                    'Success',
                    'Utilisateur activer !'
                );
                return $this->redirectToRoute('app_login');
            }
            

        }
        // ici un probléme se pose de token 
        $this->addFlash('danger','le token est invalid ou a expiré');
        return $this->redirectToRoute('app_login');
        
          
    }

}
