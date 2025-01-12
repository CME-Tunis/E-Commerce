<?php

namespace App\Form;
use App\Enum\Status; 
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomP')
            ->add('image', FileType::class, [
                'label' => 'Image ( JPEG, PNG ou JPG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '4048k'
                    ])
                ]
            ])
            ->add('stock')
            ->add('description')
            ->add('ProdCategory', null)
            ->add('status', ChoiceType::class, [
                'choices' => Status::cases(),
                'choice_label' => fn(Status $status) => $status->value,
                'choice_value' => fn(?Status $status) => $status?->value,
            ])
            ->add('prix')
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
    
}
