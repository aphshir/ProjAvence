<?php

namespace App\Twig\Components;

use App\Entity\CreditCard;
use App\Entity\User;
use App\Form\CreditCardCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent('CreditCardForm', template: 'components/CreditCardForm.html.twig')]
class CreditCardForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public ?User $user = null;

    #[LiveProp(writable: true)]
    public bool $isSuccess = false;

    #[LiveProp(writable: true)]
    public bool $isCheckoutMode = false;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        $creditCards = [];
        if ($this->user) {
            $creditCards = $this->user->getCreditCards()->toArray();
        }

        return $this->createForm(CreditCardCollectionType::class, [
            'creditCards' => $creditCards,
        ]);
    }

    #[LiveAction]
    public function save(): ?RedirectResponse
    {
        $this->submitForm();

        $form = $this->getForm();
        if (!$form->isValid()) {
            return null;
        }

        $formData = $form->getData();
        $submittedCards = $formData['creditCards'] ?? [];
        $existingCards = $this->user->getCreditCards();

        $submittedCardIds = [];
        foreach ($submittedCards as $card) {
            if ($card instanceof CreditCard) {
                $card->setUser($this->user);
                if ($card->getId()) {
                    $submittedCardIds[] = $card->getId();
                } else {
                    $this->user->addCreditCard($card);
                    $this->entityManager->persist($card);
                }
            }
        }

        foreach ($existingCards->toArray() as $existingCard) {
            if ($existingCard->getId() && !in_array($existingCard->getId(), $submittedCardIds)) {
                $this->user->removeCreditCard($existingCard);
                $this->entityManager->remove($existingCard);
            }
        }

        $this->entityManager->flush();

        if ($this->isCheckoutMode) {
            return $this->redirectToRoute('app_checkout');
        }

        $this->isSuccess = true;

        $this->formValues = [
            'creditCards' => array_map(function (CreditCard $card) {
                return [
                    'number' => $card->getNumber(),
                    'expirationDate' => $card->getExpirationDate(),
                    'cvv' => $card->getCvv(),
                ];
            }, $this->user->getCreditCards()->toArray()),
        ];

        return null;
    }
}
