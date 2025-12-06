<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentCategory = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.categories.form.name',
                'attr' => [
                    'placeholder' => 'admin.categories.form.name_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.categories.validation.name_required',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'admin.categories.validation.name_too_long',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.categories.form.description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'admin.categories.form.description_placeholder',
                    'rows' => 4,
                ],
            ])
            ->add('parent', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'admin.categories.form.parent_category',
                'required' => false,
                'placeholder' => 'admin.categories.form.no_parent',
                'query_builder' => function (EntityRepository $er) use ($currentCategory) {
                    $qb = $er->createQueryBuilder('c')
                        ->where('c.parent IS NULL')
                        ->orderBy('c.name', 'ASC');

                    if ($currentCategory && $currentCategory->getId() && $currentCategory->getParent() === null) {
                        $qb->andWhere('c.id != :currentId')
                           ->setParameter('currentId', $currentCategory->getId());
                    }

                    return $qb;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
