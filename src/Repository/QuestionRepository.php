<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

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
     * @param $pollId
     * @return Question[] Returns an array of Question objects
     */
    public function findByPoll($pollId)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.poll = :pollId')
            ->setParameter('pollId', $pollId)
            ->orderBy('q.questionNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByPollId($id)
    {
        try {
            return $this->getEntityManager()
                ->createQuery(
                    'SELECT  q.id,q.question
                FROM App:Question q
                WHERE q.poll = :id'
                )
                ->setParameter('id', $id)
                ->getResult();
        } catch (NonUniqueResultException $e) {
        }
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
