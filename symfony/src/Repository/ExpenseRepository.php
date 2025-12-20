<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findByMonth(\DateTime $startDate, \DateTime $endDate, User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.date >= :startDate')
            ->andWhere('e.date < :endDate')
            ->andWhere('e.user = :user')
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wszystkie wydatki cykliczne dla użytkownika
     */
    public function findRecurringExpenses(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.recurringFrequency > :frequency')
            ->andWhere('e.user = :user')
            ->setParameter('frequency', 0)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajduje wydatki cykliczne z wystąpieniami w danym miesiącu
     */
    public function findRecurringExpensesForMonth(int $year, int $month, User $user): array
    {
        $startDate = new \DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('+1 month -1 day');

        return $this->createQueryBuilder('e')
            ->where('e.recurringFrequency > :frequency')
            ->andWhere('e.user = :user')
            ->setParameter('frequency', 0)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
