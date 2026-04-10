<?php

namespace App\Form;

use App\Entity\Agriculteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AgriculteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresseferme', TextareaType::class, [
                'label' => 'Adresse de la ferme',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse de la ferme est obligatoire']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('superficieferme', NumberType::class, [
                'label' => 'Superficie (hectares)',
                'scale' => 2,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La superficie est obligatoire']),
                    new Assert\Positive(['message' => 'La superficie doit être un nombre positif']),
                    new Assert\Range([
                        'min' => 0.1,
                        'max' => 10000,
                        'notInRangeMessage' => 'La superficie doit être entre {{ min }} et {{ max }} hectares'
                    ])
                ]
            ])
            ->add('typeCulture', ChoiceType::class, [
                'label' => 'Type de culture',
                'placeholder' => '-- Sélectionnez --',
                'choices' => [
                    'Céréales' => 'cereales',
                    'Maraîchage' => 'maraichage',
                    'Arboriculture' => 'arboriculture',
                    'Viticulture' => 'viticulture',
                    'Élevage' => 'elevage',
                    'Oléiculture' => 'oleiculture',
                    'Polyculture' => 'polyculture',
                    'Autre' => 'autre'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un type de culture'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agriculteur::class,
        ]);
    }
}