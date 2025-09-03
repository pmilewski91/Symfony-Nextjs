<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Find conflicting reservations for a given room and time period
     */
    public function findConflictingReservations(int $roomId, \DateTime $startDateTime, \DateTime $endDateTime, ?int $excludeReservationId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.room = :roomId')
            ->andWhere('(r.startDateTime < :endDateTime AND r.endDateTime > :startDateTime)')
            ->setParameter('roomId', $roomId)
            ->setParameter('startDateTime', $startDateTime)
            ->setParameter('endDateTime', $endDateTime);

        if ($excludeReservationId !== null) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeReservationId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Check if there are any conflicting reservations
     */
    public function hasConflictingReservations(int $roomId, \DateTime $startDateTime, \DateTime $endDateTime, ?int $excludeReservationId = null): bool
    {
        return count($this->findConflictingReservations($roomId, $startDateTime, $endDateTime, $excludeReservationId)) > 0;
    }

    /**
     * Get reservations for a specific room
     */
    public function findByRoom(int $roomId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.room = :roomId')
            ->setParameter('roomId', $roomId)
            ->orderBy('r.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reservations with filtering options
     */
    public function findWithFilters(?int $roomId = null, ?\DateTime $dateFrom = null, ?\DateTime $dateTo = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.room', 'room')
            ->addSelect('room');

        if ($roomId !== null) {
            $qb->andWhere('r.room = :roomId')
               ->setParameter('roomId', $roomId);
        }

        if ($dateFrom !== null) {
            $qb->andWhere('r.startDateTime >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo !== null) {
            $qb->andWhere('r.endDateTime <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        return $qb->orderBy('r.startDateTime', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
