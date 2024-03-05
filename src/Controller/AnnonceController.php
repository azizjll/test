<?php

namespace App\Controller;




use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Repository\AnnonceRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Annonce;
use App\Entity\Commentaire;

use Symfony\Component\HttpFoundation\Request;
use App\Form\AnnonceType;
use App\Form\CommentaireType;
use App\Form\SearchAnnonceType;
use DateTime;

//flasy
use MercurySeries\FlashyBundle\FlashyNotifier;


//upload images
use Symfony\Component\String\Slugger\SluggerInterface;


class AnnonceController extends AbstractController
{
    #[Route('/annonce', name: 'app_annonce')]
    public function index(): Response
    {
        return $this->render('annonce/index.html.twig', [
            'controller_name' => 'AnnonceController',
        ]);
    }
    #[Route('/annonce/excel', name: 'annonceexcel')]
    public function exportAnnoncesToExcel(Request $request, AnnonceRepository $annonceRepository)
    {
        // Fetch annonces
        $annonces = $annonceRepository->findAll();

        // Create a new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Add a worksheet
        $worksheet = $spreadsheet->getActiveSheet();

        // Set headers
        $worksheet->setCellValue('A1', 'ID'); // Add ID field
        $worksheet->setCellValue('B1', 'Titre');
        $worksheet->setCellValue('C1', 'Description');
        $worksheet->setCellValue('D1', 'Date');
        //$worksheet->setCellValue('E1', 'Image'); // Assuming you have an 'image' field

        // Fill data
        $row = 2; // Start from row 2, as row 1 has headers
        foreach ($annonces as $annonce) {
            // Include more fields as needed based on your Annonce entity
            $worksheet->setCellValue('A' . $row, $annonce->getId());  // Add ID
            $worksheet->setCellValue('B' . $row, $annonce->getTitre());
            $worksheet->setCellValue('C' . $row, $annonce->getDescription());
            $worksheet->setCellValue('D' . $row, $annonce->getDate()->format('Y-m-d H:i:s'));
            // $worksheet->setCellValue('E' . $row, $annonce->getImage()); // Assuming 'image' field exists

            $row++;
        }

        // Create the writer
        $writer = new Xlsx($spreadsheet);

        // Create a temporary filename
        $filename = 'annonces-export.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);

        // Save the file
        $writer->save($tempFile);

        // Return the file as a response
        return $this->file($tempFile, $filename);
    }
    #[Route('/showdbannonce', name: 'showdbannonce', methods: ['GET', 'POST'])]
    public function showdbannonce(AnnonceRepository $annonceRepository, Request $request, Request $request2): Response
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(SearchAnnonceType::class);
        $search = $form->handleRequest($request2);
        // Default query for initial page load
        $query = $em->getRepository(Annonce::class)->createQueryBuilder('a')->orderBy('a.id', 'DESC')->getQuery();
        //$users = $joueurRepository->findAllUsers();
        $annonces = new Paginator($query);
        $currentPage = $request->query->getInt('page', 1);
        $itemsPerPage = 3;
        $annonces
            ->getQuery()
            ->setFirstResult($itemsPerPage * ($currentPage - 1))
            ->setMaxResults($itemsPerPage);

        $totalItems = count($annonces);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        if ($form->isSubmitted() && $form->isValid()) {
            /*$mots = $search->get('mots')->getData();
            $query = $annonceRepository->searchQuery($mots);*/

            $annonces = $annonceRepository->search($search->get('mots')->getData());
            $currentPage = $request->query->getInt('page', 1);
            $totalItems = count($annonces);
            $pagesCount = ceil($totalItems / $itemsPerPage);
            return $this->render('annonce/showdbannonce.html.twig', [
                'annonces' => $annonces,
                'CurrentPage' => $currentPage,
                'pagesCount' => $pagesCount,
                'form' => $form->createView()
                //'pagination' => $pagination,
                // 'annonces' => $pagination->getItems(), // Get paginated results
            ]); // Filter query based on search terms
        }

        /* Pagination setup
        $paginator = $this->get('paginator'); // Inject the paginator service
        $page = $request->query->getInt('page', 1); // Get current page number from request
        $itemsPerPage = 3; // Adjust as needed

        // Apply pagination to the query
        $pagination = $paginator->paginate(
            $query,
            $page,
            $itemsPerPage
        );*/

        return $this->render('annonce/showdbannonce.html.twig', [
            'annonces' => $annonces,
            'CurrentPage' => $currentPage,
            'pagesCount' => $pagesCount,
            'form' => $form->createView()
        ]);
    }

    #[Route('/showannonce/{id}', name: 'showannonce')]
    public function showannonce($id, ManagerRegistry $managerRegistry,Request $request): Response
    {
        $annonce = $this->getDoctrine()->getRepository(Annonce::class)->find($id);


        //partie reponse
        //creation d'une reponse
        $commentaire = new Commentaire();
        $commentaire->setUser($this->getUser());
        //generer le formlaire
        $commentform = $this->createForm(CommentaireType::class, $commentaire);
        $commentform->handleRequest($request);
        //traitement du formulaire
        if($commentform->isSubmitted() && $commentform->isValid()){
            $commentaire->setDate(new DateTime());

            $commentaire->setAnnonce($annonce);

            $em = $this->getDoctrine()->getManager();
            $em->persist($commentaire);

            $em->flush();
            return $this->redirectToRoute('showannonce', ['id' => $annonce->getId()]);



        }
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
            'commentform' => $commentform->createView(),
        ]);
    }






    #[Route('/deletannonce/{id}', name: 'deletannonce')]
    public function deletannonce($id, ManagerRegistry $managerRegistry, AnnonceRepository $repo,FlashyNotifier $flashy): Response
    {
        $em = $managerRegistry->getManager();
        $id = $repo->find($id);
        $em->remove($id);
        $em->flush();
        $flashy->success('Annonce supprimée', 'http://your-awesome-link.com');

        return $this->redirectToRoute('showdbannonce');
    }

    #[Route('/addannonce', name: 'addannonce')]
    public function addannonce(ManagerRegistry $managerRegistry, Request $req,SluggerInterface $slugger,FlashyNotifier $flashy): Response
    {
        $em = $managerRegistry->getManager();
        $annonce = new Annonce();
        $annonce->setUser($this->getUser());
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();

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
                $annonce->setBrochureFilename($newFilename);
            }

            // ... persist the $product variable or any other work
            $annonce->setDate(new DateTime());

            $em->persist($annonce);
            $em->flush();

            $flashy->success('Annonce ajoutée avec succèes', 'http://your-awesome-link.com');


            return $this->redirect('showdbannonce');
        }
        return $this->renderForm('annonce/addannonce.html.twig', [
            'f' => $form
        ]);
    }


    #[Route('/editannonce/{id}', name: 'editannonce')]
    public function editannonce($id, AnnonceRepository
    $annonceRepository, ManagerRegistry $managerRegistry, Request $req,SluggerInterface $slugger,FlashyNotifier $flashy): Response
    {

        $em = $managerRegistry->getManager();
        $dataid = $annonceRepository->find($id);

        $form = $this->createForm(AnnonceType::class, $dataid);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();

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
                $dataid->setBrochureFilename($newFilename);
            }

            // ...
            $em->persist($dataid);
            $em->flush();
            $flashy->success('Annonce modifiée avec succèes', 'http://your-awesome-link.com');

            return $this->redirectToRoute('showdbannonce');
        }

        return $this->renderForm('annonce/addannonce.html.twig', [
            'f' => $form
        ]);
    }




}
?>