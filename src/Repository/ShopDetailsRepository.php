<?php

namespace App\Repository;

use App\Entity\ShopDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopDetails>
 *
 * @method ShopDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopDetails[]    findAll()
 * @method ShopDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopDetails::class);
    }

//    /**
//     * @return ShopDetails[] Returns an array of ShopDetails objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ShopDetails
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
