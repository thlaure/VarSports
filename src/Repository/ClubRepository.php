<?php

namespace App\Repository;

use App\Entity\Club;
use App\Entity\Discipline;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    /**
     * @param Discipline[]  $disciplines
     * @param string[]|null $orderBy
     *
     * @return Club[]
     */
    public function searchClub(string $term, array $disciplines = [], array $cities = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.name LIKE :value')
            ->setParameter('value', '%'.$term.'%');

        if (count($disciplines) > 0) {
            $qb->leftJoin('c.disciplines', 'd')
                ->addSelect('d')
                ->andWhere('d IN (:disciplines)')
                ->setParameter('disciplines', $disciplines);
        }

        if (count($cities) > 0) {
            $qb->andWhere('c.postalCode IN (:postal_codes)')
                ->setParameter('postal_codes', $cities);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy('c.'.$field, $direction);
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        $result = $qb->getQuery()->getResult();
        if (!\is_array($result)) {
            $result = [];
        }

        return $result;
    }

    public function getCities(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.city, c.postalCode')
            ->distinct()
            ->orderBy('c.city', 'ASC')
            ->andWhere('c.city IS NOT NULL AND c.postalCode IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Club[] Returns an array of Club objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Club
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
