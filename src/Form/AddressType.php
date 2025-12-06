<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'profile.address.form.street',
                'attr' => [
                    'placeholder' => 'profile.address.form.street_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.address.validation.street_required',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'profile.address.validation.street_too_long',
                    ]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'profile.address.form.postal_code',
                'attr' => [
                    'placeholder' => 'profile.address.form.postal_code_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.address.validation.postal_code_required',
                    ]),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'profile.address.validation.postal_code_too_long',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'profile.address.form.city',
                'attr' => [
                    'placeholder' => 'profile.address.form.city_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.address.validation.city_required',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'profile.address.validation.city_too_long',
                    ]),
                ],
            ])
            ->add('country', CountryType::class, [
                'label' => 'profile.address.form.country',
                'placeholder' => 'profile.address.form.country_placeholder',
                'preferred_choices' => ['FR', 'BE', 'CH', 'CA'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'profile.address.validation.country_required',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
