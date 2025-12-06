<?php

namespace App\Twig\Components;

use App\Entity\CreditCard;
use App\Entity\User;
use App\Form\CreditCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('SingleCreditCardForm', template: 'components/SingleCreditCardForm.html.twig')]
class SingleCreditCardForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?CreditCard $creditCard = null;

    #[LiveProp]
    public bool $isEdit = false;

    #[LiveProp]
    public ?string $redirectUrl = null;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CreditCardType::class, $this->creditCard ?? new CreditCard());
    }

    #[LiveAction]
    public function save(): RedirectResponse
    {
        $this->submitForm();

        $form = $this->getForm();
        if (!$form->isValid()) {
            return $this->redirectToRoute('app_profile');
        }

        /** @var CreditCard $creditCard */
        $creditCard = $form->getData();

        if (!$this->isEdit) {
            /** @var User $user */
            $user = $this->getUser();
            $creditCard->setUser($user);
            $this->entityManager->persist($creditCard);
        }

        $this->entityManager->flush();

        if ($this->redirectUrl === 'checkout') {
            return $this->redirectToRoute('app_checkout');
        }

        return $this->redirectToRoute('app_profile');
    }
}
