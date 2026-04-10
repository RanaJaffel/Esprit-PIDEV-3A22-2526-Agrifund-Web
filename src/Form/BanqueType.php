<?php

namespace App\Form;

use App\Entity\Banque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BanqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codebanque', TextType::class, [
                'label' => 'Code banque',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code banque est obligatoire']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 10,
                        'minMessage' => 'Le code banque doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le code banque ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-z0-9]+$/',
                        'message' => 'Le code banque ne peut contenir que des lettres et des chiffres'
                    ])
                ]
            ])
            ->add('representantLegal', TextType::class, [
                'label' => 'Représentant légal',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le représentant légal est obligatoire']),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le nom du représentant doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom du représentant ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\'-]+$/',
                        'message' => 'Le nom contient des caractères invalides'
                    ])
                ]
            ])
            ->add('addresseSiege', TextareaType::class, [
                'label' => 'Adresse du siège social',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse du siège est obligatoire']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('adresseAgence', TextareaType::class, [
                'label' => 'Adresse de l\'agence principale',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('siteweb', UrlType::class, [
                'label' => 'Site web officiel',
                'required' => false,
                'default_protocol' => 'https',
                'constraints' => [
                    new Assert\Url(['message' => 'Veuillez entrer une URL valide'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Banque::class,
        ]);
    }
}