<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Question $entity, bool $flush = true): void
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
    public function remove(Question $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @return Question[] Returns an array of Tag objects
     */
    public function findByNameAndGroup(string $searchString, ?int $groupId = null, int $page = 1, $limit = 20): array
    {
        $qb = $this->createQueryBuilderBySearchAndPage($searchString, $page, $limit);
        if (\is_null($groupId)) {
            $qb = $qb->andWhere('q.groupId is null');
        } else {
            $qb = $qb
                ->andWhere('q.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    private function createQueryBuilderBySearchAndPage(string $searchString, int $page, int $limit): QueryBuilder
    {
        $page   = max(1, $page);
        $limit  = max(1, $limit);
        $offset = ($page - 1) * $limit;
        $qb     = $this->createQueryBuilder('q');
        if (!empty($searchString)) {
            $qb = $qb
                ->andWhere($qb->expr()->like('q.title', ':likeName'))
                ->setParameter('likeName', '%' . $searchString . '%');
        }

        return $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    }

    /**
     * @return Question[] Returns an array of Tag objects
     */
    public function findByNameAndUserVkId(string $searchString, int $userVkId, int $page, int $limit): array
    {
        return $this
            ->createQueryBuilderBySearchAndPage($searchString, $page, $limit)
            ->join('q.author', 'a')
            ->andWhere('a.vkId = :vkId')
            ->setParameter('vkId', $userVkId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int[]
     */
    public function findCommunities(int $userVkId): array
    {
        $qb = $this->createQueryBuilder('q');

        return $qb
            ->select('q.groupId')
            ->andWhere('q.groupId is not null')
            ->distinct()
            ->getQuery()
            ->getArrayResult();
    }

    public function findByNameAndUserVkIdAndGroupId(string $searchString, int $userVkId, int $groupId, int $page, int $limit) {
        return $this
            ->createQueryBuilderBySearchAndPage($searchString, $page, $limit)
            ->join('q.author', 'a')
            ->andWhere('a.vkId = :vkId')
            ->andWhere('q.groupId = :groupId')
            ->setParameter('groupId', $groupId)
            ->setParameter('vkId', $userVkId)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
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
    public function findOneBySomeField($value): ?Question
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
