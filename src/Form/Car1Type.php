<?php

namespace App\Form;

use App\Entity\Car;
use App\Entity\Category;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class Car1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' =>'title',
            ])

            ->add('title')
            ->add('keywords')
            ->add('description')

            ->add('image', FileType::class,[
                'label' =>'Car Main Image',

                //unmapped means that this field is not associated to any entity property
                'mapped' =>false,

                //make it optional so you don't have to re-upload the PDF file
                //everytime you edit the Product details
                'required' =>false,

                //unmapped fields can't define their validation using annotations
                //in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' =>[
                            'image/*',   //All image types
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image File',
                    ])
                ],
            ])

            ->add('marka', ChoiceType::class, [
                'choices' => [
                    'Mercedes' => 'Mercedes',
                    'Ford' => 'Ford',
                    'Opel' => 'Opel',
                    'Audi' => 'Audi',
                    'Volvo' => 'Volvo',
                    'Mitsubishi' => 'Mitsubishi',
                    'Renault' => 'Renault',
                    'Toyota' => 'Toyota',
                    'Hyundai' => 'Hyundai',
                    'Peugeot' => 'Peugeot',
                    'Volkswage' => 'Volkswagen',
                    'BMW' => 'BMW',
                    'Cadillac' => 'Cadillac',


                ],
            ])
            ->add('model')
            ->add('caryear')
            ->add('fuel')
            ->add('transmission')
            ->add('capacity')
            ->add('kilometre')
            ->add('price')
            ->add('airbag')

            ->add('city', ChoiceType::class,[
                'choices' => [
                    'Ankara' => 'Ankara',
                    'İstanbul' => 'İstanbul',
                    'Antalya' => 'Antalya',
                    'Paris' => 'Paris',
                    'Moscow' => 'Moscow',
                    'Barcelona' => 'Barcelona' ],
            ])



            ->add('country', ChoiceType::class, [
                'choices' => [
                    'Turkiye' => 'Turkiye',
                    'Spain' => 'Spain',
                    'Greece' => 'Greece',
                    'Russia' => 'Russia',
                    'France' => 'France' ],

            ])
            ->add('location')

            ->add('detail', CKEditorType::class, array(
                'config' => array(
                    'uiColor' => '#ffffff',
                    //...
                ),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
        ]);
    }
}
