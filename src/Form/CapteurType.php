<?php

namespace App\Form;

use App\Entity\Capteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CapteurType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('typeCapteur', ChoiceType::class, [
                'label' => 'Type de capteur',
                'choices' => [
                    '🌡️ Température'     => 'TEMPERATURE',
                    '💧 Humidité du sol' => 'HUMIDITE_SOL',
                    '🧪 pH du sol'       => 'PH_SOL',
                    '🌧️ Pluviométrie'    => 'PLUVIOMETRIE',
                    '☀️ Luminosité'      => 'LUMINOSITE',
                    '💨 Vent'            => 'VENT',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('modele', TextType::class, [
                'label'    => 'Modèle du capteur',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Simulateur Python'
                ]
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Zone Nord du champ'
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => [
                    '✅ Actif'    => 'ACTIF',
                    '❌ Inactif' => 'INACTIF',
                ],
                'attr' => ['class' => 'form-select']
            ])

            // ✅ AJOUT idproject
            ->add('idproject', IntegerType::class, [
                'label' => '🌾 ID du Projet Agricole',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 1',
                    'min'         => 1
                ],
                'help'  => 'Entrez l\'ID du projet auquel ce capteur appartient'
            ])

            ->add('enregistrer', SubmitType::class, [
                'label' => '💾 Enregistrer',
                'attr'  => [
                    'class' => 'btn btn-success w-100 mt-3'
                ]
            ])
            

            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Capteur::class,
        ]);
    }
}