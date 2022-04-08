<?php

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Answer $entity, bool $flush = true): void
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
    public function remove(Answer $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Answer[]
     */
    public function findByNameAndUserVkId(string $searchString, int $userVkId, int $page, int $limit): array
    {
        return $this
            ->createQueryBuilderBySearchAndPage($searchString, $userVkId, $page, $limit)
            ->getQuery()
            ->getResult();
    }

    private function createQueryBuilderBySearchAndPage(
        string $searchString,
        int $userVkId,
        int $page,
        int $limit
    ): QueryBuilder {
        $page   = max(1, $page);
        $limit  = max(1, $limit);
        $offset = ($page - 1) * $limit;
        $qb     = $this->createQueryBuilder('a');
        if (!empty($searchString)) {
            $qb = $qb
                ->andWhere($qb->expr()->like('a.title', ':likeName'))
                ->setParameter('likeName', '%' . $searchString . '%');
        }

        return $qb
            ->join('a.author', 'author')
            ->andWhere('author.vkId = :vkId')
            ->setParameter('vkId', $userVkId)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }

    /**
     * @return Answer[]
     */
    public function findByNameAndUserVkIdAndGroupId(
        string $searchString,
        int $userVkId,
        int $groupId,
        int $page,
        int $limit
    ): array {
        return $this
            ->createQueryBuilderBySearchAndPage($searchString, $userVkId, $page, $limit)
            ->join('a.question', 'q')
            ->andWhere('q.groupId = :groupId')
            ->setParameter('groupId', $groupId)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Answer[] Returns an array of Answer objects
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
    public function findOneBySomeField($value): ?Answer
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
