<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
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

    public function findEventsByParticipationCount()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.participations', 'p')
            ->select('e.nom, COUNT(p.id) as participationCount')
            ->groupBy('e.id')
            ->orderBy('participationCount', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByNameAndLocation($keyword = null, $location = null)
    {
        $qb = $this->createQueryBuilder('e');

        if ($keyword !== null) {
            $qb->andWhere('e.nom LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($location !== null) {
            $qb->andWhere('e.lieu LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
