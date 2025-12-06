<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private Security $security,
        private CartRepository $cartRepository,
        private ProductRepository $productRepository
    ) {
    }

    public function getCart(): Cart
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $cart = $this->cartRepository->findOneBy(['user' => $user], ['updatedAt' => 'DESC']);

            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($user);
                $this->entityManager->persist($cart);
                $this->mergeSessionCart($cart);
                $this->entityManager->flush();
            }

            return $cart;
        }

        return $this->getSessionCart();
    }

    public function add(Product $product, int $quantity = 1): bool
    {
        if ($product->getStock() < $quantity) {
            return false;
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            return $this->addToDbCart($product, $quantity);
        }

        return $this->addToSessionCart($product, $quantity);
    }

    public function updateQuantity(Product $product, int $quantity): bool
    {
        if ($quantity > 0 && $product->getStock() < $quantity) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->remove($product);
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            return $this->updateDbCartQuantity($product, $quantity);
        }

        return $this->updateSessionCartQuantity($product, $quantity);
    }

    public function remove(Product $product): bool
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            return $this->removeFromDbCart($product);
        }

        return $this->removeFromSessionCart($product);
    }

    public function clear(): void
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $cart = $this->getCart();
            $cart->clear();
            $this->entityManager->flush();
        } else {
            $session = $this->requestStack->getSession();
            $session->remove(self::CART_SESSION_KEY);
        }
    }

    public function getItemCount(): int
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $cart = $this->getCart();
            return $cart->getItemCount();
        }

        $sessionCart = $this->getSessionCartData();
        $count = 0;
        foreach ($sessionCart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getTotal(): float
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $cart = $this->getCart();
            return $cart->getTotal();
        }

        $sessionCart = $this->getSessionCartData();
        $total = 0.0;
        foreach ($sessionCart as $item) {
            $product = $this->productRepository->find($item['productId']);
            if ($product) {
                $total += $product->getPrice() * $item['quantity'];
            }
        }
        return $total;
    }

    public function syncSessionToDb(User $user): void
    {
        $sessionCart = $this->getSessionCartData();

        if (empty($sessionCart)) {
            return;
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user], ['updatedAt' => 'DESC']);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
        }

        foreach ($sessionCart as $item) {
            $product = $this->productRepository->find($item['productId']);
            if ($product && $product->getStock() >= $item['quantity']) {
                $existingItem = null;
                foreach ($cart->getItems() as $cartItem) {
                    if ($cartItem->getProduct()->getId() === $product->getId()) {
                        $existingItem = $cartItem;
                        break;
                    }
                }

                if ($existingItem) {
                    $newQuantity = $existingItem->getQuantity() + $item['quantity'];
                    if ($product->getStock() >= $newQuantity) {
                        $existingItem->setQuantity($newQuantity);
                    }
                } else {
                    $cartItem = new CartItem();
                    $cartItem->setCart($cart);
                    $cartItem->setProduct($product);
                    $cartItem->setQuantity($item['quantity']);
                    $this->entityManager->persist($cartItem);
                    $cart->addItem($cartItem);
                }
            }
        }

        $this->entityManager->flush();

        $session = $this->requestStack->getSession();
        $session->remove(self::CART_SESSION_KEY);
    }

    private function getSessionCart(): Cart
    {
        $sessionCart = $this->getSessionCartData();
        $cart = new Cart();

        foreach ($sessionCart as $item) {
            $product = $this->productRepository->find($item['productId']);
            if ($product) {
                $cartItem = new CartItem();
                $cartItem->setProduct($product);
                $cartItem->setQuantity($item['quantity']);
                $cart->addItem($cartItem);
            }
        }

        return $cart;
    }

    private function getSessionCartData(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::CART_SESSION_KEY, []);
    }

    private function setSessionCartData(array $cartData): void
    {
        $session = $this->requestStack->getSession();
        $session->set(self::CART_SESSION_KEY, $cartData);
    }

    private function addToSessionCart(Product $product, int $quantity): bool
    {
        $sessionCart = $this->getSessionCartData();
        $productId = $product->getId();

        if (isset($sessionCart[$productId])) {
            $newQuantity = $sessionCart[$productId]['quantity'] + $quantity;
            if ($product->getStock() < $newQuantity) {
                return false;
            }
            $sessionCart[$productId]['quantity'] = $newQuantity;
        } else {
            $sessionCart[$productId] = [
                'productId' => $productId,
                'quantity' => $quantity,
            ];
        }

        $this->setSessionCartData($sessionCart);
        return true;
    }

    private function updateSessionCartQuantity(Product $product, int $quantity): bool
    {
        $sessionCart = $this->getSessionCartData();
        $productId = $product->getId();

        if (isset($sessionCart[$productId])) {
            $sessionCart[$productId]['quantity'] = $quantity;
            $this->setSessionCartData($sessionCart);
            return true;
        }

        return false;
    }

    private function removeFromSessionCart(Product $product): bool
    {
        $sessionCart = $this->getSessionCartData();
        $productId = $product->getId();

        if (isset($sessionCart[$productId])) {
            unset($sessionCart[$productId]);
            $this->setSessionCartData($sessionCart);
            return true;
        }

        return false;
    }

    private function addToDbCart(Product $product, int $quantity): bool
    {
        $cart = $this->getCart();

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $newQuantity = $item->getQuantity() + $quantity;
                if ($product->getStock() < $newQuantity) {
                    return false;
                }
                $item->setQuantity($newQuantity);
                $this->entityManager->flush();
                return true;
            }
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);

        $this->entityManager->persist($cartItem);
        $cart->addItem($cartItem);
        $this->entityManager->flush();

        return true;
    }

    private function updateDbCartQuantity(Product $product, int $quantity): bool
    {
        $cart = $this->getCart();

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $item->setQuantity($quantity);
                $this->entityManager->flush();
                return true;
            }
        }

        return false;
    }

    private function removeFromDbCart(Product $product): bool
    {
        $cart = $this->getCart();

        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $cart->removeItem($item);
                $this->entityManager->remove($item);
                $this->entityManager->flush();
                return true;
            }
        }

        return false;
    }

    private function mergeSessionCart(Cart $dbCart): void
    {
        $sessionCart = $this->getSessionCartData();

        foreach ($sessionCart as $item) {
            $product = $this->productRepository->find($item['productId']);
            if ($product && $product->getStock() >= $item['quantity']) {
                $cartItem = new CartItem();
                $cartItem->setCart($dbCart);
                $cartItem->setProduct($product);
                $cartItem->setQuantity($item['quantity']);
                $this->entityManager->persist($cartItem);
                $dbCart->addItem($cartItem);
            }
        }

        $session = $this->requestStack->getSession();
        $session->remove(self::CART_SESSION_KEY);
    }
}
