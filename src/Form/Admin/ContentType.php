<?php

namespace App\Form\Admin;

use App\Entity\Admin\Content;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')

            ->add('image', FileType::class,[
                'label'=> 'Car Gallery Image',

                //unmapped means that this field is not associated to any entity property
                'mapped' => false,

                //make it optional so you don't have to re-upload the PDF file
                //everytime you edit the Product details
                'required' => false,

                //unmapped fields can't define their validation using annotations
                //in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' => [
                            'image/*', //all image types
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File' ,
                    ])
                ],
            ])

            ->add('price')
            ->add('number')

            ->add('status', ChoiceType::class,[
                'choices' => [
                    'True' => 'True' ,
                    'False' => 'False' ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
