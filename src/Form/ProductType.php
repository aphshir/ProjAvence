<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Enum\ProductStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'admin.products.form.name',
                'attr' => [
                    'placeholder' => 'admin.products.form.name_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.name_required',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'admin.products.validation.name_too_long',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'admin.products.form.description',
                'attr' => [
                    'placeholder' => 'admin.products.form.description_placeholder',
                    'rows' => 6,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.description_required',
                    ]),
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'admin.products.form.price',
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'admin.products.form.price_placeholder',
                    'step' => '0.01',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.price_required',
                    ]),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'admin.products.validation.price_must_be_positive',
                    ]),
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'admin.products.form.stock',
                'attr' => [
                    'placeholder' => 'admin.products.form.stock_placeholder',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.stock_required',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'admin.products.validation.stock_must_be_positive',
                    ]),
                ],
            ])
            ->add('status', EnumType::class, [
                'class' => ProductStatus::class,
                'label' => 'admin.products.form.status',
                'choice_label' => fn (ProductStatus $status) => $status->label(),
                'placeholder' => 'admin.products.form.status_placeholder',
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.status_required',
                    ]),
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'admin.products.form.category',
                'placeholder' => 'admin.products.form.category_placeholder',
                'constraints' => [
                    new NotBlank([
                        'message' => 'admin.products.validation.category_required',
                    ]),
                ],
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'label' => 'admin.products.form.images',
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'images-collection',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
