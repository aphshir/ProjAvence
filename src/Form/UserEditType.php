<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'admin.users.form.email',
                'attr' => [
                    'placeholder' => 'admin.users.form.email_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.users.validation.email_required',
                    ]),
                    new Email([
                        'message' => 'admin.users.validation.email_invalid',
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'admin.users.form.first_name',
                'attr' => [
                    'placeholder' => 'admin.users.form.first_name_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.users.validation.first_name_required',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'admin.users.validation.first_name_too_long',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'admin.users.form.last_name',
                'attr' => [
                    'placeholder' => 'admin.users.form.last_name_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.users.validation.last_name_required',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'admin.users.validation.last_name_too_long',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'admin.users.form.roles',
                'choices' => [
                    'admin.users.form.role_user' => 'ROLE_USER',
                    'admin.users.form.role_admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.users.validation.roles_required',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
