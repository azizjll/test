<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{


    #[Route('/calendar', name: 'calendarevent', methods: ['GET'])]
    public function calendarEvent(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $calendarEvents = [];

        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => $event->getId(),
                'title' => $event->getNom(),
                'start' => $event->getDate()->format('Y-m-d\TH:i:s'),
            ];
        }

        return $this->render('event/calendar.html.twig', [
            'calendarEvents' => json_encode($calendarEvents),
            'initialDate' => (new \DateTime())->format('Y-m-d'),
        ]);
    }
    #[Route('/recherche-evenement', name: 'recherche_evenement', methods: ['GET'])]
    public function searchEvent(Request $request, EventRepository $eventRepository): Response
    {
        $keyword = $request->query->get('q');
        $location = $request->query->get('location');

        // Si ni le nom ni le lieu ne sont renseignés, vous pouvez rediriger ou afficher un message.
        if ($keyword === null && $location === null) {
            // Redirection ou gestion de l'erreur
            // ...
        }

        $events = $eventRepository->searchByNameAndLocation($keyword, $location);

        return $this->render('event/indexf.html.twig', [
            'events' => $events,
            'pagination' => false,
        ]);
    }

    #[Route('/front', name: 'app_event_indexf', methods: ['GET'])]
    public function indexf(EventRepository $eventRepository, PaginatorInterface $paginator ,Request $request): Response
    {
        $query = $eventRepository->createQueryBuilder('e')->orderBy('e.id', 'DESC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),  // Current page
            5  // Items per page
        );

        return $this->render('event/indexf.html.twig', [
            'events' => $pagination,
            'pagination' => true,
        ]);
    }
   
    
    #[Route('/dash/dashboard', name: 'dash', methods: ['GET'])]
    public function dashboard(EventRepository $eventRepository): Response
    {
        return $this->render('back.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/creerevent', name: 'creerevent', methods: ['GET', 'POST'])]
    public function creerevent(Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger, UserRepository $userRepository): Response
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
                  $event->setUser($this->getUser());
              }
            $entityManager->persist($event);
            $entityManager->flush();

            // Send email to all users
            $users = $userRepository->findAll();
            foreach ($users as $user) {
                $transport = Transport::fromDsn('smtp://hannachieya41@gmail.com:fqcgphhourupzuqu@smtp.gmail.com:587');

// Create a Mailer object
                $mailer = new Mailer($transport);

                $email = (new Email());

// Set the "From address"
                $email->from('hannachieya41@gmail.com');

// Set the "To address"
                $email->to(
                    $user->getEmail()
                );

                $email->subject('A new event is created!');

                $email->html('<p>A new event has been created. <a href="http://127.0.0.1:8000/event/front">Check here!</a></p>');

                try {
                    // Send email
                    $mailer->send($email);

                    return $this->redirectToRoute('ajouterevent', [], Response::HTTP_SEE_OTHER);

                } catch (TransportExceptionInterface $e) {
                    // Display custom error message
                    die('<style>* { font-size: 100px; color: #fff; background-color: #ff4e4e; }</style><pre><h1>&#128544;Error!</h1></pre>');

                }
            }

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

    #[Route('/dash/statnb', name: 'statnb', methods: ['GET'])]
    public function statnb(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findEventsByParticipationCount();

        // $events est maintenant un tableau d'événements triés par le nombre de participations

        return $this->render('event/stat.html.twig', [
            'events' => $events,
        ]);
    }


    #[Route('/eve/new', name: 'app_event_new', methods: ['GET', 'POST'])]
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
                  $event->setUser($this->getUser());
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

  

    #[Route('/show/{id}', name: 'app_event_show', methods: ['GET'])]
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
    

    #[Route('/delete/{id}', name: 'app_event_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {

            $entityManager->remove($event);
            $entityManager->flush();


        return $this->redirectToRoute('ajouterevent', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('bcc/{id}', name: 'app_event_deletef', methods: ['GET','POST'])]
    public function deleteb(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_indexf', [], Response::HTTP_SEE_OTHER);
    }




    #[Route('/update/{id}/modifierevent', name: 'modifierevent', methods: ['GET', 'POST'])]
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

