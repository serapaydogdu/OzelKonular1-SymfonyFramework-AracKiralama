<?php

namespace App\Form;

use App\Entity\Category;
use Doctrine\DBAL\Types\TextType;
use phpDocumentor\Reflection\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('parentid',EntityType::class,[
//                'class' => Category::class,
//                'choice_label' =>'title',
//            ])
            ->add('parentid',ChoiceType::class,[
                'choices'=>[
                    'Cars'=>'1',
                    'Car Rental'=>'2',
                    'Premium'=>'3',
                    'Luxury'=>'4',
                    'Economic'=>'5',
                    'Comfort'=>'6',
                    'Prestige'=>'7',
                    'Normal'=>'8',
                    'Special'=>'9',
                    'Family'=>'10',

                ],
            ])
            ->add('title',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['label' => 'Category Name'])
            ->add('keywords')
            ->add('description')

            ->add('image', FileType::class,[
                'label' =>'Category Image',

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

            ->add('status', ChoiceType::class,[
                'choices' => [
                    'True' => 'True' ,
                    'False' => 'False' ],
            ])        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'csrf_protection' => false,
        ]);
    }
}
