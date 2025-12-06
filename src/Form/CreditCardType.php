<?php

namespace App\Form;

use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CreditCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'profile.card.form.number',
                'attr' => [
                    'placeholder' => 'profile.card.form.number_placeholder',
                    'maxlength' => 19,
                    'autocomplete' => 'cc-number',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.card.validation.number_required',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{13,19}$/',
                        'message' => 'profile.card.validation.number_invalid',
                    ]),
                ],
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'profile.card.form.expiration_date',
                'attr' => [
                    'placeholder' => 'profile.card.form.expiration_date_placeholder',
                    'maxlength' => 7,
                    'autocomplete' => 'cc-exp',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.card.validation.expiration_date_required',
                    ]),
                    new Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/[0-9]{2,4}$/',
                        'message' => 'profile.card.validation.expiration_date_invalid',
                    ]),
                ],
            ])
            ->add('cvv', TextType::class, [
                'label' => 'profile.card.form.cvv',
                'attr' => [
                    'placeholder' => 'profile.card.form.cvv_placeholder',
                    'maxlength' => 4,
                    'autocomplete' => 'cc-csc',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.card.validation.cvv_required',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{3,4}$/',
                        'message' => 'profile.card.validation.cvv_invalid',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditCard::class,
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
    }
}
