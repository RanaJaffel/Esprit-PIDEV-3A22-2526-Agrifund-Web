<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BanqueProfileInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addresseSiege', TextType::class, [
                'label' => 'Adresse du siège',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Adresse du siège social'],
            ])
            ->add('representantLegal', TextType::class, [
                'label' => 'Représentant légal',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom du représentant légal'],
            ])
            ->add('adresseAgence', TextType::class, [
                'label' => 'Adresse de l\'agence',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Adresse de l\'agence'],
            ])
            ->add('siteweb', UrlType::class, [
                'label' => 'Site web',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://www.banque.com'],
            ])
            ->add('logoFile', FileType::class, [
                'label' => 'Logo de la banque',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}