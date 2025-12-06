<?php

namespace App\Repository;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findAllOrderedByDate(): array
    {
        return $this->findAllOrderedByDateQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByDateQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('customerOrder')
            ->orderBy('customerOrder.createdAt', 'DESC');
    }

    public function findLatestOrders(int $limit = 5): array
    {
        return $this->createQueryBuilder('customerOrder')
            ->leftJoin('customerOrder.user', 'user')
            ->addSelect('user')
            ->leftJoin('customerOrder.orderItems', 'orderItem')
            ->addSelect('orderItem')
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getMonthlySalesForDeliveredOrders(int $months = 12): array
    {
        $startDate = new \DateTime("-{$months} months");
        $startDate->modify('first day of this month');
        $startDate->setTime(0, 0, 0);

        $orders = $this->createQueryBuilder('customerOrder')
            ->leftJoin('customerOrder.orderItems', 'orderItem')
            ->addSelect('orderItem')
            ->where('customerOrder.status = :status')
            ->andWhere('customerOrder.createdAt >= :startDate')
            ->setParameter('status', OrderStatus::LIVREE)
            ->setParameter('startDate', $startDate)
            ->orderBy('customerOrder.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $monthlySales = [];
        foreach ($orders as $order) {
            $monthKey = $order->getCreatedAt()->format('Y-m');
            if (!isset($monthlySales[$monthKey])) {
                $monthlySales[$monthKey] = [
                    'month' => (int) $order->getCreatedAt()->format('m'),
                    'year' => (int) $order->getCreatedAt()->format('Y'),
                    'total' => 0.0,
                ];
            }
            $monthlySales[$monthKey]['total'] += $order->getTotal();
        }

        $result = [];
        $currentDate = clone $startDate;
        $endDate = new \DateTime();

        while ($currentDate <= $endDate) {
            $key = $currentDate->format('Y-m');
            $result[] = $monthlySales[$key] ?? [
                'month' => (int) $currentDate->format('m'),
                'year' => (int) $currentDate->format('Y'),
                'total' => 0.0,
            ];
            $currentDate->modify('+1 month');
        }

        return $result;
    }

    public function countTotal(): int
    {
        return $this->createQueryBuilder('customerOrder')
            ->select('COUNT(customerOrder.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
