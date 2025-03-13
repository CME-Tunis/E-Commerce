<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
/**
 * @extends ServiceEntityRepository<Panier>
 *
 * @method Panier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Panier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Panier[]    findAll()
 * @method Panier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Panier::class);
        $this->entityManager = $entityManager;
    }

    private EntityManagerInterface $entityManager;

    public function getTotalPanierByUser(User $user): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(p.prixTotale)') // On récupère uniquement la somme
            ->andWhere('p.panierUser = :user') // Filtrage par utilisateur connecté
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult(); // Récupérer un seul résultat (la somme totale)
    }
    
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.panierUser = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
    public function save(Panier $panier): void
    {
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
    }
    public function getQuantiteTotaleParUtilisateur($user): int
{
    return $this->createQueryBuilder('p')
        ->select('SUM(p.quantite)')
        ->where('p.panierUser = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleScalarResult();
}

//    /**
//     * @return Panier[] Returns an array of Panier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Panier
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
