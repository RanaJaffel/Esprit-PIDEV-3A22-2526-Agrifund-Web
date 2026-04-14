<?php

namespace App\Form;

use App\Entity\Agriculteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgriculteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adresseferme', TextType::class, [
                'label' => 'Adresse de la ferme',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse complète de votre exploitation'
                ],
            ])
            ->add('superficieferme', NumberType::class, [
                'label' => 'Superficie de la ferme (hectares)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Superficie en hectares',
                    'step' => '0.01',
                    'min' => '0'
                ],
            ])
            ->add('typeCulture', ChoiceType::class, [
                'label' => 'Type de culture',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'choices' => [
                    'Choisissez un type' => '',
                    'Céréales' => 'cereales',
                    'Maraîchage' => 'maraichage',
                    'Arboriculture' => 'arboriculture',
                    'Viticulture' => 'viticulture',
                    'Élevage' => 'elevage',
                    'Agriculture biologique' => 'bio',
                    'Polyculture' => 'polyculture',
                    'Autre' => 'autre',
                ],
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agriculteur::class,
        ]);
    }
}