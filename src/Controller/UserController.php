<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use App\Service\JWTService;
use App\Form\UserType;




class UserController extends AbstractController
{

    private $userRepository;
    private $tokenGenerator;
    private $mail;
    private $passwordEncoder;
    private $jwt;




    public function __construct(
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        SendMailService $mail,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTService $jwt,
        )
    {
        $this->userRepository=$userRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->mail = $mail;
        $this->jwt = $jwt;
        $this->passwordEncoder = $passwordEncoder;
        
    }

    #[Route('/', name: 'app_task')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig'
        );
    }

    #[Route('/back', name: 'app_back')]
    public function back(): Response
    {
        return $this->render('task/back.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    //CRUD CLIENT PARTIE ADMIN
    #[Route('/listClients', name: 'app_list_Clients')]
    public function listClients(): Response
    {
        $clients=$this->userRepository->findByRole('ROLE_CLIENT');
        return $this->render('listClients.html.twig', [
            'clients' => $clients,
        ]);
    }
    #[Route('/listCoach', name: 'app_list_Coach')]
    public function listCoach(): Response
    {
        $clients=$this->userRepository->findByRole('ROLE_COACH');
        return $this->render('listCoatch.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/addClient', name: 'app_add_client')]
    public function addClient(Request $request,SendMailService $mail,ManagerRegistry $managerRegistry)
    {
        $user = new User();

    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()){
        $token = $this->tokenGenerator->generateToken();
        $user->setResetToken($token);
        $password_hashed = $this->passwordEncoder->encodePassword($user,$user->getPassword());
        $user->setPassword($password_hashed);
        $user->setEtat(true);
        $user->setRoles(['ROLE_CLIENT']);
        $user = $form->getData();

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
            $token = $this->jwt->generate($header, $payload , $this->getParameter('app.jwtsecret'));
             

            // On envoie un mail
            $mail->send(
                'no-reply@monsite.com',
                $user->getEmail(),
                'Activation de votre compte',
                'register',
                compact('user','token')
            );
            
            $this->addFlash(
                'Success',
                'Client added successfully! !'
            );

        return $this->redirectToRoute('app_list_Clients');

    }

    return $this->render('addClient.html.twig', [
        'Client' => $form->createView(),
    ]);
    }

    #[Route('/bloqueClient/{id}', name: 'app_block_client')]
    public function bloqueClients($id,ManagerRegistry $managerRegistry): Response
    {
        $clients=$this->userRepository->find($id);

        $clients->setEtat(false);

        $entityManager = $managerRegistry ->getManager();
        $entityManager->persist($clients);
        $entityManager->flush();
        $this->addFlash(
            'Success',
            ''.$clients->getUserIdentifier().' blocked successfully!!'
        );
        return $this->redirectToRoute('app_list_Clients');
    }

    #[Route('/debloqueClient/{id}', name: 'app_deblock_client')]
    public function debloqueClients($id,ManagerRegistry $managerRegistry)
    {
        $client = $this->userRepository->find($id);
        $client->setEtat(true);
        $entityManager = $managerRegistry ->getManager();
        $entityManager->persist($client);
        $entityManager->flush();
        $this->addFlash(
            'Success',
            ''.$client->getUserIdentifier().' deblocked successfully!'
        );
        return $this->redirectToRoute('app_list_Clients');
    }
    #[Route('/oubli-pass', name: 'app_oublie_pass')]
    public function forgetPass(Request $request,TokenGeneratorInterface $tokenGeneratorInterface ,EntityManagerInterface $entityManager,SendMailService $mail)
    {
        $form=$this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        
        
        if($form->isSubmitted() && $form->isValid()){
             //On va chercher l'utilisateur par email

             $user=$this->userRepository->findOneByEmail($form->get('email')->getData());
              //On vérifier sin on a un utilisateur 
            if($user)
            {
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();
                

                //On génere un lien de reanitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //dd($url);

                // On crée les données du mail
                $context = compact('url','user');
                // Envoi du mail
                $mail->send(
                    'no-reply@gymmonstre.tn',
                    $user->getEmail(),
                    'Réanitialisation de mot de passe',
                    'reset_password',
                    $context
                );

                $this->addFlash(
                    'Success',
                    'Email envoyer avec succès !'
                );
                return $this->redirectToRoute('app_login');



            }
            //User est null
            $this->addFlash(
                'Danger',
                'Email n\'existe pas !'
            );
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/reset_password_request.html.twig',[
            'requestPassForm' =>$form->createView()
        ]);
    }

    #[Route('/reset-pass/{token}', name:'reset_pass') ]
    public function resetPass( string $token,
    Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher):Response
    {
        // On vérifie ci on a ce token dans le base de données
        $user = $this->userRepository->findOneByResetToken($token);
        //$user = $this->userRepository->findBy(array('resetToken' => $token))
        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $user->setResetToken('');
               // $password_hashed = $this->passwordEncoder->encodePassword($user,$form->get('password')->getData());
                $user->setPassword($passwordHasher->hashPassword($user,$form->get('password')->getData()));
                
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash(
                    'Success',
                    'Mot de passe changer avec succés !'
                );
    
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]); 
        }
        
        $this->addFlash(
            'Danger',
            'Jeton invalid !'
        );
        return $this->redirectToRoute('app_login');


    }


}
