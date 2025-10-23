<?php


namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Entity\Author;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    
    #[Route('/book/afficher', name: 'app_book_list')]
    public function afficher(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();

        return $this->render('book/afficher.html.twig', [
            'books' => $books
        ]);
    }

    #[Route('/book/affiche', name: 'app_book_affiche')]
public function Affiche(BookRepository $bookRepository): Response
{
    $publishedBooks = $bookRepository->findBy(['published' => true]);
    $numPublishedBooks = count($publishedBooks);
    $numUnPublishedBooks = count($bookRepository->findBy(['published' => false]));

    return $this->render('book/affiche.html.twig', [
        'publishedBooks' => $publishedBooks,
        'numPublishedBooks' => $numPublishedBooks,
        'numUnPublishedBooks' => $numUnPublishedBooks
    ]);
}
    

    #[Route('/book/add', name: 'app_book_add')]
    public function Add(Request $request, EntityManagerInterface $entityManager){
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book -> setPublished(true);
            $author = $book->getAuthor();
            if ($author instanceof Author) {
                $author->setNbBooks($author->getNbBooks() + 1);
                $entityManager->persist($author);
                $entityManager->flush();
            }
                
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_book_list');
        }

        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);

    }

     #[Route('/edit_book/{ref}', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function editBook(BookRepository $repository, string $ref, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Fetch the book (adjust find() if 'ref' is not the primary id)
        $book = $repository->find($ref);

        if (!$book) {
            throw $this->createNotFoundException('Book not found.');
        }

        $form = $this->createForm(BookType::class, $book);
        // give the submit button a clear name/label
        $form->add('save', SubmitType::class, ['label' => 'Save changes']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If the entity is new you should call persist(); for an edit flush() is enough.
            // $entityManager->persist($book); // not needed for managed entities
            $entityManager->flush();

            $this->addFlash('success', 'Book updated successfully.');

            return $this->redirectToRoute('app_book_affiche');
        }

        return $this->render('book/edit_book.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete_book/{ref}', name: 'app_book_delete', methods: ['GET'])]
    public function deleteBook(BookRepository $repository, EntityManagerInterface $em, $ref): Response
    {
        $book = $repository->find($ref);

        if ($book) {
            $em->remove($book);
            $em->flush();

            $this->addFlash('success', 'Book supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Book non trouvé.');
        }

        return $this->redirectToRoute('app_book_affiche');
    }

    #[Route('/details/{ref}', name: 'app_book_details', methods: ['GET'])]
    public function detailsBook(BookRepository $repository, string $ref): Response
    {
        $book = $repository->find($ref);

        if (!$book) {
            throw $this->createNotFoundException('Book not found.');
        }

        return $this->render('book/details.html.twig', [
            'book' => $book,
        ]);
    }



}