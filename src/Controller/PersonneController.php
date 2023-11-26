<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Service\Helpers;
use App\Form\PersonneType;
use Psr\Log\LoggerInterface;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('personne')]
class PersonneController extends AbstractController
{
    public function __construct(private LoggerInterface $logger, private Helpers $helper) {}

    #[Route('/', name:'personne.list')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(personne::class);
        $personnes  = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/alls/age/{ageMin}/{ageMax}', name:'personne.list.age')]
    public function personneByAge($ageMin, $ageMax, PersonneRepository $repository): Response
    {
        $personnes  = $repository->findPersonneByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/stats/age/{ageMin}/{ageMax}', name:'personne.list.stats')]
    public function statspersonneByAge($ageMin, $ageMax, PersonneRepository $repository): Response
    {
        $stats  = $repository->statsPersonneByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/stats.html.twig', ['stats' => $stats[0], 'ageMin' => $ageMin, 'ageMax' => $ageMax]);
    }

    #[Route('/alls/{page?1}/{nbre?12}', name:'personne.list.alls')]
    public function indexAlls($page, $nbre, PersonneRepository $repository): Response
    {
        // dd($this->helper->sayCc());
        $nbPersonne = $repository->count([]);
        $nbrPage = ceil($nbPersonne / $nbre);
        $personnes  = $repository->findBy([], [], $nbre, ($page - 1) * $nbre);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbrPage' => $nbrPage,
            'page' => $page,
            'nbre' => $nbre,
        ]);
    }

    #[Route('/{id<\d+>}', name:'personne.detail')]
    public function detail(ManagerRegistry $doctrine, $id): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        if(!$personne) {
            $this->addFlash('error', "la personne n'existe pas ");
            return $this->redirectToRoute('personne.list');
        }
        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }

    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne($id, EntityManagerInterface $entityManager, Request $request, PersonneRepository $repository, SluggerInterface $slugger): Response
    {
        $personne = $repository->find($id);
        $new = false;
        if(!$personne) {
            $new = true;
            $personne = new Personne();
        }

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('personne_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $personne->setImage($newFilename);
            }
            $entityManager->persist($personne);
            $entityManager->flush();

            if($new) {
                $message = 'a été ajouté avec success';
            } else {
                $message = 'a été mis à jours avec success';
            }

            $this->addFlash('success', $personne->getName() . $message);
            return $this->redirectToRoute('personne.list');
        } else {
            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    #[Route('/delete/{id}', name:'personne.delete')]
    public function deletePersonne($id, EntityManagerInterface $entityManager, PersonneRepository $repository): RedirectResponse
    {
        $personne = $repository->find($id);
        if ($personne) {
            $entityManager->remove($personne);
            $entityManager->flush();
            $this->addFlash('success', 'La personne a été supprimé avec succès');
        } else {
            $this->addFlash('error', 'Personne innexistante');
        }
        return $this->redirectToRoute('personne.list.alls');
    }

    #[Route('/update/{id}/{name}/{firstname}/{age}', name:'personne.update')]
    public function updatePersonne($id, EntityManagerInterface $entityManager, PersonneRepository $repository, $name, $firstname, $age)
    {
        $personne = $repository->find($id);
        if($personne) {
            $personne->setName($name);
            $personne->setFirstName($firstname);
            $personne->setAge($age);
            $entityManager->persist($personne);
            $entityManager->flush();
            $this->addFlash('success', 'la personne a été mis à jours avec success');
        } else {
            $this->addFlash('error', 'Personne innexistante');
        }
        return $this->redirectToRoute('personne.list.alls');
    }
}