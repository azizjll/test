<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\PdfService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\RoundBlockSizeMode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistreController extends AbstractController
{

    private $userPasswordEncoderInterface;
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->userPasswordEncoderInterface=$userPasswordEncoderInterface;
    }
    #[Route('/registre', name: 'app_registre')]
    public function RegisterUser(ManagerRegistry $managerRegistry, Request $request,SendMailService $mail, JWTService  $jwt ,TokenGeneratorInterface $tokenGenerator,SluggerInterface $slugger,PdfService $pdfService): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_task');
        }
        
        $user = new User;

        $form = $this->createForm(UserType::class, $user);
        
        
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {

             /** @var UploadedFile $brochureFile */
             $brochureFile=$form->get('brochure')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setBorchureFilename($newFilename);
            }
            $file = $form->get('imageUrl')->getData();

            if ($file) {
                // Generate a unique name for the file
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
    
                // Move the file to the desired directory
                $file->move(
                    $this->getParameter('image_directory'), // Path to the directory where images will be saved
                    $fileName
                );
    
                // Update the user's profile picture property with the file name
                $user->setImageUrl($fileName); // Assuming you have a setProfilePicture method in your User entity
    
            }
            
           
            $password_hashed=$this->userPasswordEncoderInterface->encodePassword($user,$user->getPassword());
            $user->setPassword($password_hashed);
            // Accès aux rôles de l'utilisateur
            $user->getRoles();
            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);
            if(in_array('ROLE_COACH',$user->getRoles(),true)){
                $user->setEtat(false);
            }else{
                $user->setEtat(true);
            }
            $pdfHtml = $this->render('registre/index.html.twig', [
                'form' => $form->createView()
            ]);

            

            //$pdfService->showPdfFile($pdfHtml);
           /* $registrationLink = $this->generateUrl('app_registre', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // Générer le code QR avec le lien vers la route d'enregistrement
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(300)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->data($registrationLink)
                ->build();

            // Enregistrer le code QR dans le dossier temporaire
           $qrCodeFileName = 'qrcode_' . uniqid() . '.png';
            $qrCodePath = $this->getParameter('kernel.project_dir') . '/public/temp/' . $qrCodeFileName;
            $qrCode->saveToFile($qrCodePath);
            return $this->render('registre/index.html.twig', [
                'form' => $form->createView(),
                'qrCode' => $qrCode
            ])
            ;*/


            $user =$form->getData(); 
            $entityManager = $managerRegistry ->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($this->generateUrl('app_downloadQr', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL))
                ->encoding(new Encoding('UTF-8'))  // Optional, adjust encoding if needed
                ->size(300)  // Optional, adjust size
                ->margin(10)
                ->build();

            // Generate the file path
            $fileName = $user->getId() . '-qr-code.png';
            $filePath = $this->getParameter('kernel.project_dir') . '/public/temp/' . $fileName;
            $result->saveToFile($filePath);
         



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
                'no-reply@gymmonster.com',
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
            
        ])
        ;
        //return $this->render('registre/index.html.twig', [
       //     'controller_name' => 'RegistreController',
        //]);
    }
    #[Route('/verif/{token}',name:'verify_user')]
    public function verifyUser($token, JWTService $jwt,UserRepository $userRepository,EntityManagerInterface $entityManagerInterfacer):Response
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
                $entityManagerInterfacer->flush($user);
                
                

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

    #[Route('/renvoiverif' , name: 'resend_verif')]
    public function resendVerif(JWTService $jwt,SendMailService $mail,UserRepository $userRepository): Response
    {
        $user=$this->getUser();

        if(!$user){
            $this->addFlash('danger','Vous devez etre connecté pour accéder a cette page !');
            return $this->redirectToRoute('app_login');
        }

        if($user->getIsVerified()){
            $this->addFlash(
                'Warning',
                'Cet utilisateur est déja activé !'
            );
            return $this->redirectToRoute('app_login');
        }

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
                'Email de verification envoyé !'
            );
            return $this->redirectToRoute('app_login');
    }

    #[Route('/downloadQr/{id}', name: 'app_downloadQr')]
    public function downloadQr(PdfService $pdfService,int $id,UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        $form = $this->createForm(UserType::class, $user);
        $pdfHtml = $this->render('registre/index.html.twig', [
            'form' => $form->createView()
        ]);

        $pdfService->showPdfFile($pdfHtml);

        
    }


}
