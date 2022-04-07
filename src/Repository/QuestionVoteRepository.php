<?php

namespace App\Repository;

use App\Entity\QuestionVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method QuestionVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuestionVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuestionVote[]    findAll()
 * @method QuestionVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionVote::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(QuestionVote $entity, bool $flush = true): void
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
    public function remove(QuestionVote $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function sum(int $userVkId, \App\Entity\Question $question): int
    {
        try {
            return (int)$this->createQueryBuilder('q')
                ->join('q.author', 'a')
                ->join('q.question', 'question')
                ->andWhere('a.vkId = :vkId')
                ->andWhere('question.id = :questionId')
                ->setParameter('vkId', $userVkId)
                ->setParameter('questionId', $question->getId())
                ->select('sum(q.value) as voteSum')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }

    // /**
    //  * @return QuestionVote[] Returns an array of QuestionVote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QuestionVote
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
