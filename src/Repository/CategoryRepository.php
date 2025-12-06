<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('category')
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRootCategories(): array
    {
        return $this->createQueryBuilder('category')
            ->where('category.parent IS NULL')
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllHierarchical(): array
    {
        $hierarchicalCategories = [];

        $rootCategories = $this->findRootCategories();

        foreach ($rootCategories as $rootCategory) {
            $this->addCategoryWithChildren($rootCategory, $hierarchicalCategories);
        }

        return $hierarchicalCategories;
    }

    private function addCategoryWithChildren(Category $category, array &$result): void
    {
        $result[] = $category;

        $children = $category->getChildren()->toArray();
        usort($children, function (Category $a, Category $b) {
            return strcmp($a->getName(), $b->getName());
        });

        foreach ($children as $child) {
            $this->addCategoryWithChildren($child, $result);
        }
    }

    public function findAllWithCounts(): array
    {
        return $this->createQueryBuilder('category')
            ->select('category', 'COUNT(DISTINCT product.id) as productCount', 'COUNT(DISTINCT children.id) as childrenCount')
            ->leftJoin('category.products', 'product')
            ->leftJoin('category.children', 'children')
            ->groupBy('category.id')
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllHierarchicalWithCounts(): array
    {
        $countsQuery = $this->createQueryBuilder('category')
            ->select('category.id', 'COUNT(product.id) as productCount')
            ->leftJoin('category.products', 'product')
            ->groupBy('category.id')
            ->getQuery()
            ->getResult();

        $directCounts = [];
        foreach ($countsQuery as $row) {
            $directCounts[$row['id']] = (int) $row['productCount'];
        }

        $hierarchicalCategories = $this->findAllHierarchical();

        $cumulativeCounts = [];
        foreach ($hierarchicalCategories as $category) {
            if (!isset($cumulativeCounts[$category->getId()])) {
                $cumulativeCounts[$category->getId()] = $this->calculateCumulativeCount($category, $directCounts, $cumulativeCounts);
            }
        }

        $result = [];
        foreach ($hierarchicalCategories as $category) {
            $result[] = [
                'category' => $category,
                'productCount' => $cumulativeCounts[$category->getId()],
            ];
        }

        return $result;
    }

    private function calculateCumulativeCount(Category $category, array $directCounts, array &$cache): int
    {
        if (isset($cache[$category->getId()])) {
            return $cache[$category->getId()];
        }

        $count = $directCounts[$category->getId()] ?? 0;

        foreach ($category->getChildren() as $child) {
            $count += $this->calculateCumulativeCount($child, $directCounts, $cache);
        }

        $cache[$category->getId()] = $count;

        return $count;
    }

    public function countProducts(Category $category): int
    {
        return $this->createQueryBuilder('category')
            ->select('COUNT(product.id)')
            ->leftJoin('category.products', 'product')
            ->where('category.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function hasChildren(Category $category): bool
    {
        $count = $this->createQueryBuilder('category')
            ->select('COUNT(children.id)')
            ->leftJoin('category.children', 'children')
            ->where('category.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function canBeDeleted(Category $category): bool
    {
        return !$this->hasChildren($category) && $this->countProducts($category) === 0;
    }
}
