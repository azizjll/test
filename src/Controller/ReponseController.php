<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\ReponseRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Reponse;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ReponseType;

class ReponseController extends AbstractController
{
    #[Route('/reponse', name: 'app_reponse')]
    public function index(): Response
    {
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
        ]);
    }

    #[Route('/showreponse/{reponse}', name: 'app_showreponse')]
    public function showreponse($reponse): Response
    {
        
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse
        ]);
    }

    #[Route('/addreponse', name: 'app_addreponse')]
    public function addreponse(Request $request): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Redirect or do something else after successful form submission
            return $this->redirectToRoute('your_redirect_route');
        }

        return $this->renderForm('annonce/addreponse.html.twig', [
            'reponseform' => $form
        ]);
    }


    #[Route('/editreponse/{id}', name: 'editreponse')]
    public function editreponse($id, ReponseRepository
    $reponseRepository, ManagerRegistry $managerRegistry, Request $req): Response
    {
        
        $em = $managerRegistry->getManager();
        $dataid = $reponseRepository->find($id);
       
        $form = $this->createForm(ReponseType::class, $dataid);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showdbannonce');
        }

        return $this->renderForm('reponse/reponseedit.html.twig', [
            'f' => $form
        ]);
    }

}
