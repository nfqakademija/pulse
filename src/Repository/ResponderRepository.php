<?php

namespace App\Repository;

use App\Entity\Responder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Responder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Responder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Responder[]    findAll()
 * @method Responder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Responder::class);
    }

    /**
     * @param $team_lead_id
     * @return array
     */
    public function findRespondersSlackIdByAdminId($team_lead_id):array
    {
        $q = $this->createQueryBuilder('r')
            ->select('r.slackId')
            ->andWhere('r.teamLead = :team_lead_id')
            ->setParameter('team_lead_id', $team_lead_id);

        return $q->getQuery()->getArrayResult();
    }
}
