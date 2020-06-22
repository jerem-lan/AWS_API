<?php

namespace App\Repository;

use App\Entity\CloudStorageProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CloudStorageProvider|null find($id, $lockMode = null, $lockVersion = null)
 * @method CloudStorageProvider|null findOneBy(array $criteria, array $orderBy = null)
 * @method CloudStorageProvider[]    findAll()
 * @method CloudStorageProvider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CloudStorageProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CloudStorageProvider::class);
    }

    // /**
    //  * @return CloudStorageProvider[] Returns an array of CloudStorageProvider objects
    //  */
    
    public function findByUser($id)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from(CloudStorageProvider::class, 'a')
            ->where('a.User = :User')
            ->setParameter('User', $id);
        return $qb->getQuery()
                ->getArrayResult();
        ;
    }
    
    public function findByUserAndService($id, $service)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a.id')
            ->from(CloudStorageProvider::class, 'a')
            ->where('a.User = :User AND a.service = :service')
            ->setParameter('User', $id)
            ->setParameter('service', $service);
        return $qb->getQuery()
                ->getArrayResult();
        ;
    }
}
