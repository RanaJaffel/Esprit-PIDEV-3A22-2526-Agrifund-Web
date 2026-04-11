<?php

namespace App\Form;

use App\Entity\ReleveHebdomadaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveHebdomadaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['disabled_fields'];

        $builder
            ->add('idproject', IntegerType::class, ['disabled' => $disabled])

            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'disabled' => $disabled,
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'disabled' => $disabled,
            ])

            ->add('tempMoyenne', NumberType::class, ['disabled' => $disabled])
            ->add('humiditeMoyenne', NumberType::class, ['disabled' => $disabled])

            ->add('conditionDominante', TextType::class, ['disabled' => $disabled])
            ->add('nbMesuresTotal', IntegerType::class, ['disabled' => $disabled])

            ->add('dateGeneration', DateTimeType::class, [
                'widget' => 'single_text',
                'disabled' => $disabled,
                // ⚠️ pour éviter les soucis DateTimeImmutable/DateTime selon ton Entity
                'input' => 'datetime',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReleveHebdomadaire::class,
            'disabled_fields' => true,
        ]);
    }
}