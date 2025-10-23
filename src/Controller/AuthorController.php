<?php
namespace App\Controller;
use App\Repository\AuthorRepository;
use App\Entity\Author; // utile si tu t’en sers (ex. pour calculer totalBooks)
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AuthorType;
use Doctrine\ORM\EntityManagerInterface;

class AuthorController extends AbstractController
{
    #[Route('/author/{name}', name: 'app_author_show')]
    public function showAuthor(string $name): Response
    {
        return $this->render('author/show.html.twig', [
            'name' => $name
        ]);
    }
    #[Route('/authors', name: 'app_authors_list')]
    public function listAuthors(): Response
    {
        $authors = [
            [
                'id' => 1,
                'picture' => '/images/Victor-Hugo.jpg',
                'username' => 'Victor Hugo',
                'email' => 'victor.hugo@gmail.com',
                'nb_books' => 100
            ],
            [
                'id' => 2,
                'picture' => '/images/william-shakespeare.jpg',
                'username' => 'William Shakespeare',
                'email' => 'william.shakespeare@gmail.com',
                'nb_books' => 200
            ],
            [
                'id' => 3,
                'picture' => '/images/Taha_Hussein.jpg',
                'username' => 'Taha Hussein',
                'email' => 'taha.hussein@gmail.com',
                'nb_books' => 300
            ],
        ];

        return $this->render('author/list.html.twig', [
            'authors' => $authors
        ]);
    }
        #[Route('/author/details/{id}', name: 'app_author_details')]
    public function authorDetails(int $id): Response
    {
        // Les mêmes données que dans listAuthors()
        $authors = [
            [
                'id' => 1,
                'picture' => '/images/Victor-Hugo.jpg',
                'username' => 'Victor Hugo',
                'email' => 'victor.hugo@gmail.com',
                'nb_books' => 100
            ],
            [
                'id' => 2,
                'picture' => '/images/william-shakespeare.jpg',
                'username' => 'William Shakespeare',
                'email' => 'william.shakespeare@gmail.com',
                'nb_books' => 200
            ],
            [
                'id' => 3,
                'picture' => '/images/Taha_Hussein.jpg',
                'username' => 'Taha Hussein',
                'email' => 'taha.hussein@gmail.com',
                'nb_books' => 300
            ],
        ];

        // Chercher l'auteur par ID
        $selectedAuthor = null;
        foreach ($authors as $author) {
            if ($author['id'] === $id) {
                $selectedAuthor = $author;
                break;
            }
        }

        return $this->render('author/showAuthor.html.twig', [
            'author' => $selectedAuthor
        ]);
    }

    
    #[Route('/Affiche', name: 'app_Affiche', methods: ['GET'])]
    public function Affiche(AuthorRepository $repository): Response
    {
        // Récupère tous les auteurs (tri possible par username)
        $authors = $repository->findBy([], ['username' => 'ASC']);

        return $this->render('author/Affiche.html.twig', [
            'authors' => $authors,
        ]);
    }

    


        
    #[Route('/AddStatistique', name: 'app_AddStatistique', methods: ['GET'])]
    public function addStatistique(AuthorRepository $repository): Response
    {
        $author1 = (new Author())
            ->setUsername('test')
            ->setEmail('test@gmail.com');
            // ->setPicture('/images/test.jpg')
            // ->setNbBooks(0)
        ;

        $repository->add($author1, true);   // ✅ flush immédiat
        $this->addFlash('success', 'Auteur ajouté avec succès.');
        return $this->redirectToRoute('app_Affiche');
    }

    #[Route('/Add', name: 'app_add', methods: ['GET', 'POST'])]
    public function addAuthor(Request $request, EntityManagerInterface $entityManager)
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($author);
            $entityManager->flush();

            return $this->redirectToRoute('app_Affiche');
        }

        return $this->render('author/Add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
   
    #[Route('/edit/{id}', name: 'app_edit', methods: ['GET', 'POST'])]
    public function editAuthor(AuthorRepository $repository, $id, Request $request, EntityManagerInterface $entityManager)
    {
        $author = $repository->find($id);
        $form = $this->createForm(AuthorType::class, $author);
        $form->add('edit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_Affiche');
        }
        return $this->render('author/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'app_delete', methods: ['GET'])]
    public function deleteAuthor(AuthorRepository $repository, $id): Response
    {
        $author = $repository->find($id);
        if ($author) {
            $repository->remove($author, true);
            $this->addFlash('success', 'Auteur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Auteur non trouvé.');
        }
        return $this->redirectToRoute('app_Affiche');
    }


    #[Route('/authors/by-email', name: 'app_authors_by_email')]
    public function listAuthorsByEmail(AuthorRepository $repository): Response
    {
        $authors = $repository->listAuthorByEmail();
        return $this->render('author/Affiche.html.twig', [
            'authors' => $authors,
        ]);
    }
    #[Route('/author/search-by-books', name: 'app_author_search_books')]
public function searchAuthors(Request $request, AuthorRepository $repository): Response
{
    $min = (int) $request->query->get('min', 0);
    $max = (int) $request->query->get('max', 100);
    $authors = $repository->findAuthorsByBookCount($min, $max);

    return $this->render('author/search_books.html.twig', [
        'authors' => $authors,
        'min' => $min,
        'max' => $max
    ]);
}

#[Route('/author/delete-empty', name: 'app_author_delete_empty')]
public function deleteEmptyAuthors(AuthorRepository $repository): Response
{
    $count = $repository->deleteAuthorsWithNoBooks();
    $this->addFlash('success', "$count authors deleted.");
    return $this->redirectToRoute('app_Affiche');
}



    }
