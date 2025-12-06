<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatus;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'app_checkout', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartService->getCart();

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('warning', 'checkout.cart_empty');
            return $this->redirectToRoute('app_cart');
        }

        $user = $this->getUser();
        $selectedAddressId = $request->query->get('selected_address');

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
            'addresses' => $user->getAddresses(),
            'creditCards' => $user->getCreditCards(),
            'selectedAddressId' => $selectedAddressId,
        ]);
    }

    #[Route('/validate', name: 'app_checkout_validate', methods: ['POST'])]
    public function validate(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->cartService->getCart();

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('error', 'checkout.cart_empty');
            return $this->redirectToRoute('app_cart');
        }

        $addressId = $request->request->get('address_id');
        $creditCardId = $request->request->get('credit_card_id');

        if (!$addressId || !$creditCardId) {
            $this->addFlash('error', 'checkout.missing_information');
            return $this->redirectToRoute('app_checkout');
        }

        $user = $this->getUser();

        $address = null;
        foreach ($user->getAddresses() as $userAddress) {
            if ($userAddress->getId() == $addressId) {
                $address = $userAddress;
                break;
            }
        }

        if (!$address) {
            $this->addFlash('error', 'checkout.invalid_address');
            return $this->redirectToRoute('app_checkout');
        }

        $creditCard = null;
        foreach ($user->getCreditCards() as $userCard) {
            if ($userCard->getId() == $creditCardId) {
                $creditCard = $userCard;
                break;
            }
        }

        if (!$creditCard) {
            $this->addFlash('error', 'checkout.invalid_card');
            return $this->redirectToRoute('app_checkout');
        }

        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();
            if ($product->getStock() < $cartItem->getQuantity()) {
                $this->addFlash('error', 'checkout.insufficient_stock');
                return $this->redirectToRoute('app_checkout');
            }
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus(OrderStatus::EN_PREPARATION);

        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();

            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setProductPrice($product->getPrice());

            $this->entityManager->persist($orderItem);
            $order->addOrderItem($orderItem);

            $product->setStock($product->getStock() - $cartItem->getQuantity());
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->cartService->clear();

        return $this->redirectToRoute('app_order_confirmation', ['reference' => $order->getReference()]);
    }

    #[Route('/confirmation/{reference}', name: 'app_order_confirmation', methods: ['GET'])]
    public function confirmation(string $reference): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = $this->entityManager->getRepository(Order::class)->findOneBy([
            'reference' => $reference,
            'user' => $this->getUser(),
        ]);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}
