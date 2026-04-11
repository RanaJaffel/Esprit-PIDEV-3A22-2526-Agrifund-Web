<?php

namespace App\Form;

use App\Entity\ReleveTerrain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveTerrainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ✅ choix capteurs (injecté depuis le controller)
            ->add('idCapteur', ChoiceType::class, [
                'label' => '📡 Capteur',
                'choices' => $options['capteur_choices'], // ["Capteur #1 - TEMP" => 1, ...]
                'placeholder' => 'Choisir un capteur',
                'attr' => ['class' => 'form-select'],
            ])

            // ✅ on normalise les valeurs (important pour l’IA + rapports)
            ->add('typeMesure', ChoiceType::class, [
                'label' => '📊 Type de mesure',
                'choices' => [
                    '🌡️ Température'      => 'TEMPERATURE',
                    '💧 Humidité du sol'  => 'HUMIDITE_SOL',
                    '💨 Humidité de l’air' => 'HUMIDITE_AIR',
                    '🌧️ Pluviométrie'     => 'PLUIE',
                    '☀️ Luminosité'       => 'LUMINOSITE',
                    '💨 Vitesse du vent'  => 'VENT',
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('valeurMesuree', NumberType::class, [
                'label' => '📈 Valeur mesurée',
                'scale' => 2,
                'attr'  => [
                    'class' => 'form-control',
                    'step'  => '0.01',
                ],
            ])

            ->add('unite', ChoiceType::class, [
                'label' => '📏 Unité',
                'choices' => [
                    '°C'   => '°C',
                    '%'    => '%',
                    'mm'   => 'mm',
                    'lux'  => 'lux',
                    'km/h' => 'km/h',
                ],
                'attr' => ['class' => 'form-select'],
            ])

            // ⚠️ ton entity utilise DateTimeImmutable, donc input datetime_immutable
            ->add('dateHeure', DateTimeType::class, [
                'label' => '🕒 Date & heure',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])

            // ✅ SURVEILLANCE
            ->add('qualite', ChoiceType::class, [
                'label' => '✅ Qualité',
                'choices' => [
                    'OK' => 'OK',
                    'SUSPECT' => 'SUSPECT',
                    'ERREUR_CAPTEUR' => 'ERREUR_CAPTEUR',
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('note', TextareaType::class, [
                'label' => '📝 Note (optionnel)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])

            ->add('enregistrer', SubmitType::class, [
                'label' => '💾 Enregistrer',
                'attr' => ['class' => 'btn btn-success w-100 mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReleveTerrain::class,
            'capteur_choices' => [], // ✅ option custom
        ]);
    }
}