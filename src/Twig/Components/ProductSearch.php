<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ProductSearch')]
final class ProductSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $query = '';

    #[LiveProp(writable: true, url: true)]
    public string $sort = 'name_asc';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    private const ITEMS_PER_PAGE = 12;

    public function __construct(
        private ProductRepository $productRepository,
        private PaginatorInterface $paginator
    ) {}

    public function getPagination(): PaginationInterface
    {
        $queryBuilder = $this->productRepository->searchQueryBuilder($this->query, $this->sort);

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $this->page,
            self::ITEMS_PER_PAGE,
            [
                'sortFieldParameterName' => '',
                'sortDirectionParameterName' => '',
            ]
        );

        $totalItems = $pagination->getTotalItemCount();
        $maxPage = max(1, (int) ceil($totalItems / self::ITEMS_PER_PAGE));

        if ($this->page > $maxPage) {
            $this->page = $maxPage;

            return $this->paginator->paginate(
                $queryBuilder,
                $this->page,
                self::ITEMS_PER_PAGE,
                [
                    'sortFieldParameterName' => '',
                    'sortDirectionParameterName' => '',
                ]
            );
        }

        return $pagination;
    }

    #[LiveAction]
    public function goToPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }
}
