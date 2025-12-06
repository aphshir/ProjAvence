<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllOrdered(): array
    {
        return $this->findAllOrderedQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('product')
            ->orderBy('product.name', 'ASC');
    }

    public function findAllWithCounts(): array
    {
        return $this->createQueryBuilder('product')
            ->select('product', 'COUNT(DISTINCT image.id) as imageCount', 'COUNT(DISTINCT orderItem.id) as orderItemCount')
            ->leftJoin('product.images', 'image')
            ->leftJoin('product.orderItems', 'orderItem')
            ->groupBy('product.id')
            ->orderBy('product.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countOrderItems(Product $product): int
    {
        return $this->createQueryBuilder('product')
            ->select('COUNT(orderItem.id)')
            ->leftJoin('product.orderItems', 'orderItem')
            ->where('product.id = :productId')
            ->setParameter('productId', $product->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function canBeDeleted(Product $product): bool
    {
        return $this->countOrderItems($product) === 0;
    }

    public function findAllWithSorting(string $sort = 'name_asc'): array
    {
        $queryBuilder = $this->createQueryBuilder('product')
            ->leftJoin('product.images', 'image')
            ->addSelect('image');

        switch ($sort) {
            case 'price_asc':
                $queryBuilder->orderBy('product.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('product.price', 'DESC');
                break;
            case 'newest':
                $queryBuilder->orderBy('product.id', 'DESC');
                break;
            case 'name_asc':
            default:
                $queryBuilder->orderBy('product.name', 'ASC');
                break;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function search(string $query = '', string $sort = 'name_asc'): array
    {
        return $this->searchQueryBuilder($query, $sort)
            ->getQuery()
            ->getResult();
    }

    public function searchQueryBuilder(string $query = '', string $sort = 'name_asc'): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('product')
            ->leftJoin('product.images', 'image')
            ->addSelect('image');

        if ('' !== trim($query)) {
            $queryBuilder->andWhere('LOWER(product.name) LIKE LOWER(:query) OR LOWER(product.description) LIKE LOWER(:query)')
               ->setParameter('query', '%' . trim($query) . '%');
        }

        switch ($sort) {
            case 'price_asc':
                $queryBuilder->orderBy('product.price', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('product.price', 'DESC');
                break;
            case 'newest':
                $queryBuilder->orderBy('product.id', 'DESC');
                break;
            case 'name_asc':
            default:
                $queryBuilder->orderBy('product.name', 'ASC');
                break;
        }

        return $queryBuilder;
    }

    public function findOneBySlugWithRelations(string $slug): ?Product
    {
        return $this->createQueryBuilder('product')
            ->leftJoin('product.category', 'category')
            ->addSelect('category')
            ->leftJoin('product.images', 'image')
            ->addSelect('image')
            ->where('product.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRelatedProducts(Product $product, int $limit = 6): array
    {
        $products = $this->createQueryBuilder('product')
            ->leftJoin('product.images', 'image')
            ->addSelect('image')
            ->where('product.category = :category')
            ->andWhere('product.id != :productId')
            ->setParameter('category', $product->getCategory())
            ->setParameter('productId', $product->getId())
            ->getQuery()
            ->getResult();

        shuffle($products);
        return array_slice($products, 0, $limit);
    }

    public function countByStatus(): array
    {
        $results = $this->createQueryBuilder('product')
            ->select('product.status as status, COUNT(product.id) as count')
            ->groupBy('product.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']->value] = (int) $row['count'];
        }

        return $counts;
    }

    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Product::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug);

        if ($excludeId !== null) {
            $qb->andWhere('p.id != :id')
               ->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
