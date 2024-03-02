<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\CommentaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Annonce;
use App\Entity\Commentaire;

use Symfony\Component\HttpFoundation\Request;
use App\Form\CommentaireType;

class CommentaireController extends AbstractController
{
    #[Route('/commentaire', name: 'app_commentaire')]
    public function index(): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'controller_name' => 'CommentaireController',
        ]);
    }

    #[Route('/editcomment/{ref}', name: 'editcomment')]
public function editcommentaire($ref, CommentaireRepository $commentaireRepository, Request $req): Response
{
    $commentaire = $commentaireRepository->find($ref);

    

    $annonce = $commentaire->getAnnonce();

    $form = $this->createForm(CommentaireType::class, $commentaire);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('showannonce', ['id' => $annonce->getId()]);
    }

    return $this->render('annonce/show.html.twig', [
        'annonce' => $annonce,
        'commentform' => $form->createView()
    ]);
}

//deleting comments
#[Route('/deletecommentaire/{ref}', name: 'deletecommentaire')]
public function deletcommentaire($ref, ManagerRegistry $managerRegistry, CommentaireRepository $commentaireRepository): Response
{
    $commentaire = $commentaireRepository->find($ref);

    $annonce = $commentaire->getAnnonce();
    $em = $managerRegistry->getManager();
    $em->remove($commentaire);
    $em->flush();
    return $this->redirectToRoute('showannonce', ['id' => $annonce->getId()]);
}


}
