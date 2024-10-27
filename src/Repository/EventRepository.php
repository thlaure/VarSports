<?php

namespace App\Repository;

use App\Entity\Club;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param Club[]        $clubs
     * @param int[]         $cities
     * @param string[]|null $orderBy
     *
     * @return Event[]
     */
    public function searchEvent(string $term, array $clubs = [], array $cities = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('event')
            ->andWhere('event.title LIKE :value')
            ->andWhere('event.isValidated = true')
            ->setParameter('value', '%'.$term.'%');

        if (count($clubs) > 0) {
            $qb->leftJoin('event.club', 'club')
                ->addSelect('club')
                ->andWhere('club IN (:clubs)')
                ->setParameter('clubs', $clubs);
        }

        if (count($cities) > 0) {
            $qb->innerJoin('event.city', 'city');
            $qb->andWhere('city IN (:cities)')
                ->setParameter('cities', $cities);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy('event.'.$field, $direction);
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

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
