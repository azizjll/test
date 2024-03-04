<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\AnnonceRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Annonce;
use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AnnonceType;
use App\Form\CommentaireType;
use DateTime;


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

 //add post
 


//getting the posts and their comments
    #[Route('/adminannonces', name: 'adminannonces')]
    public function adminannonces(AnnonceRepository $annonceRepository): Response
    {
        $annonces = $annonceRepository->findBy([]);
    
        return $this->render('admin/index.html.twig', [
            'annonces' => $annonces
        ]);
    }

 //deleting posts
 #[Route('/admindeletannonce/{id}', name: 'admindeletannonce')]
 public function admindeletannonce($id, ManagerRegistry $managerRegistry, AnnonceRepository $repo): Response
 {
     $em = $managerRegistry->getManager();
     $id = $repo->find($id);
     $em->remove($id);
     $em->flush();
     return $this->redirectToRoute('adminannonces');
 }

 //deleting comments
 #[Route('/admindeletecommentaire/{ref}', name: 'admindeletecommentaire')]
public function admindeletcommentaire($ref, ManagerRegistry $managerRegistry, CommentaireRepository $commentaireRepository): Response
{
    $commentaire = $commentaireRepository->find($ref);

    $annonce = $commentaire->getAnnonce();
    $em = $managerRegistry->getManager();
    $em->remove($commentaire);
    $em->flush();
    return $this->redirectToRoute('adminannonces', ['id' => $annonce->getId()]);
}
}
