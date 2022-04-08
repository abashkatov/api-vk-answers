<?php

namespace App\Repository;

use App\Entity\AnswerVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AnswerVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnswerVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnswerVote[]    findAll()
 * @method AnswerVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnswerVote::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(AnswerVote $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(AnswerVote $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function sum(int $userVkId, \App\Entity\Answer $answer): int
    {
        try {
            return (int)$this->createQueryBuilder('a')
                ->join('a.author', 'author')
                ->join('a.answer', 'answer')
                ->andWhere('author.vkId = :vkId')
                ->andWhere('answer.id = :answerId')
                ->setParameter('vkId', $userVkId)
                ->setParameter('answerId', $answer->getId())
                ->select('sum(a.value) as voteSum')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }

    // /**
    //  * @return AnswerVote[] Returns an array of AnswerVote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AnswerVote
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
