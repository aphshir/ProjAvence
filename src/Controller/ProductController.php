<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(): Response
    {
        return $this->render('products/index.html.twig');
    }

    #[Route('/products/{slug}', name: 'app_product_show')]
    public function show(string $slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBySlugWithRelations($slug);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $relatedProducts = $productRepository->findRelatedProducts($product, 6);

        return $this->render('products/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
