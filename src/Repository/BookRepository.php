<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function countRomanceBooks(): int
{
    return (int) $this->getEntityManager()
        ->createQuery('SELECT COUNT(b.ref) FROM App\Entity\Book b WHERE b.category = :cat')
        ->setParameter('cat', 'Romance')
        ->getSingleScalarResult();
}

    public function findBooksBetweenDates(\DateTime $start, \DateTime $end): array
{
    return $this->getEntityManager()
        ->createQuery('SELECT b FROM App\Entity\Book b WHERE b.publicationDate BETWEEN :start AND :end')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getResult();
}
// Recherche par ref
    public function searchBookByRef(string $ref): ?Book
    {
        return $this->createQueryBuilder('b')
            ->where('b.ref = :ref')
            ->setParameter('ref', $ref)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Liste des livres triés par auteur
    public function booksListByAuthors(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->orderBy('a.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Livres publiés avant 2023 dont l’auteur a >10 livres
    public function booksBefore2023WithAuthorsHavingMoreThan10Books(): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.author', 'a')
            ->where('b.publicationDate < :date')
            ->andWhere('a.nb_books > :count')
            ->setParameter('date', new \DateTime('2023-01-01'))
            ->setParameter('count', 10)
            ->getQuery()
            ->getResult();
    }

   public function updateCategoryScienceFictionToRomance(): int
{
    return $this->createQueryBuilder('b')
        ->update()
        ->set('b.category', ':newCategory')
        ->where('b.category = :oldCategory')
        ->setParameter('newCategory', 'Romance')
        ->setParameter('oldCategory', 'Science Fiction')
        ->getQuery()
        ->execute();
}
    }
