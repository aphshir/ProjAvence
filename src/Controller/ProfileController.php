<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\CreditCard;
use App\Entity\User;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/profile', name: 'app_profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: '')]
    public function index(): Response
    {
        $user = $this->getUser();

        $orders = $user->getOrders()->toArray();
        usort($orders, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'addresses' => $user->getAddresses(),
            'creditCards' => $user->getCreditCards(),
            'orders' => $orders,
        ]);
    }

    #[Route('/address/new', name: '_address_new')]
    public function addressNew(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($user);
            $entityManager->persist($address);
            $entityManager->flush();

            $referer = $request->query->get('redirect');
            if ($referer === 'checkout') {
                return $this->redirectToRoute('app_checkout', ['selected_address' => $address->getId()]);
            }

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/address_form.html.twig', [
            'form' => $form,
            'isEdit' => false,
        ]);
    }

    #[Route('/address/{id}/edit', name: '_address_edit')]
    public function addressEdit(
        Address $address,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('profile.address.messages.updated'));

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/address_form.html.twig', [
            'form' => $form,
            'address' => $address,
            'isEdit' => true,
        ]);
    }

    #[Route('/address/{id}/delete', name: '_address_delete', methods: ['POST'])]
    public function addressDelete(
        Address $address,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        if ($address->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($address);
        $entityManager->flush();

        $this->addFlash('success', $translator->trans('profile.address.messages.deleted'));

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/cards', name: '_cards')]
    public function cardsManage(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/cards_manage.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/card/new', name: '_card_new')]
    public function cardNew(Request $request): Response
    {
        return $this->render('profile/card_form.html.twig', [
            'isEdit' => false,
            'redirectUrl' => $request->query->get('redirect'),
        ]);
    }

    #[Route('/card/{id}/edit', name: '_card_edit')]
    public function cardEdit(CreditCard $creditCard): Response
    {
        $user = $this->getUser();

        if ($creditCard->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('profile/card_form.html.twig', [
            'creditCard' => $creditCard,
            'isEdit' => true,
        ]);
    }

    #[Route('/card/{id}/delete', name: '_card_delete', methods: ['POST'])]
    public function cardDelete(
        CreditCard $creditCard,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        if ($creditCard->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($creditCard);
        $entityManager->flush();

        $this->addFlash('success', $translator->trans('profile.card.messages.deleted'));

        return $this->redirectToRoute('app_profile');
    }
}
