<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('uploadType', ChoiceType::class, [
                'label' => 'admin.products.form.upload_type',
                'choices' => [
                    'admin.products.form.upload_type_url' => 'url',
                    'admin.products.form.upload_type_file' => 'file',
                ],
                'attr' => [
                    'data-image-upload-target' => 'typeSelector',
                    'data-action' => 'change->image-upload#toggleUploadType',
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'admin.products.form.image_url',
                'required' => false,
                'attr' => [
                    'placeholder' => 'admin.products.form.image_url_placeholder',
                    'data-image-upload-target' => 'urlField',
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'admin.products.form.image_file',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'data-image-upload-target' => 'fileField',
                    'data-action' => 'change->image-upload#previewImage',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'admin.products.validation.image_file_invalid',
                        'maxSizeMessage' => 'admin.products.validation.image_file_too_large',
                    ]),
                ],
            ])
            ->add('altText', TextType::class, [
                'label' => 'admin.products.form.image_alt',
                'required' => false,
                'attr' => [
                    'placeholder' => 'admin.products.form.image_alt_placeholder',
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'admin.products.validation.image_alt_too_long',
                    ]),
                ],
            ])
            ->add('position', HiddenType::class, [
                'attr' => ['class' => 'image-position-input'],
            ])
            ->add('isPrimary', CheckboxType::class, [
                'label' => 'admin.products.form.image_primary',
                'required' => false,
                'attr' => ['class' => 'primary-checkbox'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['uploadType'])) {
                if ($data['uploadType'] === 'url') {
                    $form->add('url', TextType::class, [
                        'label' => 'admin.products.form.image_url',
                        'required' => true,
                        'attr' => [
                            'placeholder' => 'admin.products.form.image_url_placeholder',
                            'data-image-upload-target' => 'urlField',
                        ],
                        'constraints' => [
                            new NotBlank([
                                'message' => 'admin.products.validation.image_url_required',
                            ]),
                            new Url([
                                'message' => 'admin.products.validation.image_url_invalid',
                            ]),
                            new Length([
                                'max' => 255,
                                'maxMessage' => 'admin.products.validation.image_url_too_long',
                            ]),
                        ],
                    ]);
                } else {
                    $form->add('url', TextType::class, [
                        'label' => 'admin.products.form.image_url',
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'admin.products.form.image_url_placeholder',
                            'data-image-upload-target' => 'urlField',
                        ],
                    ]);
                }

                $form->add('position', HiddenType::class, [
                    'attr' => ['class' => 'image-position-input'],
                ]);

                $form->add('isPrimary', CheckboxType::class, [
                    'label' => 'admin.products.form.image_primary',
                    'required' => false,
                    'attr' => ['class' => 'primary-checkbox'],
                ]);

                $form->add('altText', TextType::class, [
                    'label' => 'admin.products.form.image_alt',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'admin.products.form.image_alt_placeholder',
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 255,
                            'maxMessage' => 'admin.products.validation.image_alt_too_long',
                        ]),
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'csrf_protection' => false,
        ]);
    }
}
