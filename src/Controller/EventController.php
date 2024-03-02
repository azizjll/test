<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{

    #[Route('/front', name: 'app_event_indexf', methods: ['GET'])]
    public function indexf(EventRepository $eventRepository): Response
    {
        return $this->render('event/indexf.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
   
    
    #[Route('/dashboard', name: 'dash', methods: ['GET'])]
    public function dashboard(EventRepository $eventRepository): Response
    {
        return $this->render('back.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/creerevent', name: 'creerevent', methods: ['GET', 'POST'])]
    public function creerevent(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
              /** @var UploadedFile $imageFile */
              $imageFile = $form->get('image')->getData();

              if ($imageFile) {
                  $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                  $safeFilename = $slugger->slug($originalFilename);
                  $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
  
                  // Move the file to the directory where your images are stored
                  try {
                      $imageFile->move(
                          $this->getParameter('img_directory'),
                          $newFilename
                      );
                  } catch (FileException $e) {
                      // Handle the exception if something happens during the file upload
                  }
  
                  // Update the 'image' property to store the file name instead of its contents
                  $event->setImage($newFilename);
              }
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('ajouterevent', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/creerevent.html.twig', [
            'events' => $event,
            'form' => $form,
        ]);
    }


    #[Route('/ajouterevent', name: 'ajouterevent', methods: ['GET'])]
    public function ajouterevent(EventRepository $eventRepository): Response
    {
        return $this->render('event/ajouterevent.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Event $event): Response
    {
        return $this->render('event/detailevent.html.twig', [
            'event' => $event,
        ]);

    }
    
 
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    
    #[Route('/home', name: 'home', methods: ['GET'])]
    public function home(EventRepository $eventRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
   
    


    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
              /** @var UploadedFile $imageFile */
              $imageFile = $form->get('image')->getData();

              if ($imageFile) {
                  $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                  $safeFilename = $slugger->slug($originalFilename);
                  $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
  
                  // Move the file to the directory where your images are stored
                  try {
                      $imageFile->move(
                          $this->getParameter('img_directory'),
                          $newFilename
                      );
                  } catch (FileException $e) {
                      // Handle the exception if something happens during the file upload
                  }
  
                  // Update the 'image' property to store the file name instead of its contents
                  $event->setImage($newFilename);
              }
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

  

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
    #[Route('ffdf/{id}', name: 'app_event_showf', methods: ['GET'])]
    public function showd(Event $event): Response
    {
        return $this->render('event/showf.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
    
                $event->setImage($newFilename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ajouterevent', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('bcc/{id}', name: 'app_event_deletef', methods: ['POST'])]
    public function deleteb(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_indexf', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/{id}/modifierevent', name: 'modifierevent', methods: ['GET', 'POST'])]
    public function modifierevent(Request $request, Event $event, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('img_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
    
                $event->setImage($newFilename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('ajouterevent', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('event/modifierevent.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
     
  
    #[Route('/courbe-evenements', name: 'courbe_evenements', methods: ['GET'])]
    public function courbeEvenements(EventRepository $eventRepository): Response
    {
        
        $events = $eventRepository->findAll(); // Vous devrez peut-être ajuster cela en fonction de votre entité et de votre référentiel

        // Traiter les données $events pour obtenir le compte par mois
        $evenementsParMois = []; // Format : ['Janvier' => 5, 'Février' => 8, ...]
        dump($evenementsParMois);
        foreach ($events as $event) {
            $mois = $event->getDate()->format('F'); // En supposant que vous ayez un champ datetime dans votre entité Event
            if (!isset($evenementsParMois[$mois])) {
                $evenementsParMois[$mois] = 1;
            } else {
                $evenementsParMois[$mois]++;
            }
        }

        return $this->render('event/statistique.html.twig', [
            'evenementsParMois' => $evenementsParMois,
        ]);
    }


   
}
