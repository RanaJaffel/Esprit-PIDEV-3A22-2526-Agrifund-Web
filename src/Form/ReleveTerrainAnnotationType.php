<?php

namespace App\Form;

use App\Entity\ReleveTerrain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveTerrainAnnotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('qualite', ChoiceType::class, [
                'label' => '✅ Qualité',
                'choices' => [
                    'OK' => 'OK',
                    'SUSPECT' => 'SUSPECT',
                    'ERREUR_CAPTEUR' => 'ERREUR_CAPTEUR',
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => '📝 Note (optionnel)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReleveTerrain::class,
        ]);
    }
}