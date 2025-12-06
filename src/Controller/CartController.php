<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private ProductRepository $productRepository
    ) {
    }

    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(): Response
    {
        $cart = $this->cartService->getCart();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json([
                'success' => false,
                'message' => 'product_not_found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 1;

        if ($quantity < 1) {
            return $this->json([
                'success' => false,
                'message' => 'invalid_quantity',
            ], Response::HTTP_BAD_REQUEST);
        }

        $success = $this->cartService->add($product, $quantity);

        if (!$success) {
            return $this->json([
                'success' => false,
                'message' => 'insufficient_stock',
                'available' => $product->getStock(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'item_added',
            'itemCount' => $this->cartService->getItemCount(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json([
                'success' => false,
                'message' => 'product_not_found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = $data['quantity'] ?? 0;

        $success = $this->cartService->updateQuantity($product, $quantity);

        if (!$success) {
            return $this->json([
                'success' => false,
                'message' => 'insufficient_stock',
                'available' => $product->getStock(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'item_updated',
            'itemCount' => $this->cartService->getItemCount(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return $this->json([
                'success' => false,
                'message' => 'product_not_found',
            ], Response::HTTP_NOT_FOUND);
        }

        $success = $this->cartService->remove($product);

        if (!$success) {
            return $this->json([
                'success' => false,
                'message' => 'item_not_in_cart',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'item_removed',
            'itemCount' => $this->cartService->getItemCount(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->cartService->clear();
        $this->addFlash('success', 'cart.cleared');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json([
            'count' => $this->cartService->getItemCount(),
            'total' => $this->cartService->getTotal(),
        ]);
    }
}
