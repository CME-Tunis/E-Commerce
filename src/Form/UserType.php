<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'] ?? null;
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Client' => 'ROLE_CLIENT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true, // Boutons radio
                'multiple' => false, // Un seul choix possible
                'mapped' => false, // Ce champ ne mappe pas directement à l'attribut `roles`
               'data' => $user ? ($user->getRoles()[0] ?? 'ROLE_CLIENT') : 'ROLE_CLIENT', // Valeur par défaut
                'label' => 'Role',
            ])
            ->add('password', PasswordType::class, [
                'required' => false, // Laisser vide pour ne pas modifier le mot de passe
                'mapped' => false,  // Non lié directement à l’entité User
                'label' => 'Nouveau mot de passe (laisser vide pour ne pas modifier)',
            ])
            ->add('imageUser', FileType::class, [
                'label' => 'Image ( JPEG, PNG ou JPG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '4048k'
                    ])
                ]
            ])
            ->add('nom')
            ->add('prenom')
            ->add('cin')
            ->add('tel')
            ->add('isVerified')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
