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
}
