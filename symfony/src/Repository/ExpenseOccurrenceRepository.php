<?php

namespace App\Repository;

use App\Entity\ExpenseOccurrence;
use App\Entity\Expense;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseOccurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseOccurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseOccurrence[]    findAll()
 * @method ExpenseOccurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseOccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseOccurrence::class);
    }

    /**
     * Znajduje wystąpienia wydatków w przedziale dat
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate, User $user): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->andWhere('eo.user = :user')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('user', $user)
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje nieopłacone wystąpienia wydatków w przedziale dat
     */
    public function findUnpaidByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate, User $user): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->andWhere('eo.paymentStatus != :paidStatus')
            ->andWhere('eo.user = :user')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('paidStatus', 'paid')
            ->setParameter('user', $user)
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wystąpienia dla konkretnego wydatku w przedziale dat
     */
    public function findByExpenseAndDateRange(Expense $expense, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('eo')
            ->where('eo.expense = :expense')
            ->andWhere('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->setParameter('expense', $expense)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wystąpienia z pełnymi danymi wydatku (JOIN)
     */
    public function findWithExpenseData(\DateTimeInterface $startDate, \DateTimeInterface $endDate, User $user): array
    {
        return $this->createQueryBuilder('eo')
            ->join('eo.expense', 'e')
            ->leftJoin('e.category', 'c')
            ->where('eo.occurrenceDate >= :startDate')
            ->andWhere('eo.occurrenceDate <= :endDate')
            ->andWhere('eo.user = :user')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('user', $user)
            ->select('eo', 'e', 'c')
            ->orderBy('eo.occurrenceDate', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
