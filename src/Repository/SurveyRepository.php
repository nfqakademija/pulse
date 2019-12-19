<?php

namespace App\Repository;

use App\Entity\Survey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Survey|null find($id, $lockMode = null, $lockVersion = null)
 * @method Survey|null findOneBy(array $criteria, array $orderBy = null)
 * @method Survey[]    findAll()
 * @method Survey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Survey::class);
    }

    /**
     * @param $userId
     * @return Survey[] Returns an array of Survey objects
     */
    public function findByUser($userId)
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.poll', 'p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $userId)
            ->addOrderBy('s.datetime', 'DESC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneByPollId($id)
    {
        return $this->createQueryBuilder('s')
            ->select('s.id, s.name,
            p.id as pollId, p.name as pollName,
            u.id as user')
            ->innerJoin('s.poll', 'p')
            ->innerJoin('p.user', 'u')
            ->andWhere('s.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
