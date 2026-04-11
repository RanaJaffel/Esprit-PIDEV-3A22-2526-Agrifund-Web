<?php

namespace App\Form;

use App\Entity\RapportJournalier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportJournalierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['disabled_fields'];

        $builder
            ->add('dateRapport', DateType::class, [
                'widget' => 'single_text',
                'disabled' => $disabled,
            ])
            ->add('typeMesure', TextType::class, [
                'disabled' => $disabled,
            ])
            ->add('moyenne', NumberType::class, [
                'disabled' => $disabled,
            ])
            ->add('min', NumberType::class, [
                'disabled' => $disabled,
            ])
            ->add('max', NumberType::class, [
                'disabled' => $disabled,
            ])
            ->add('nbMesures', IntegerType::class, [
                'disabled' => $disabled,
            ])
            ->add('conditionDominante', TextType::class, [
                'disabled' => $disabled,
            ])
            ->add('idCapteur', IntegerType::class, [
                'disabled' => $disabled,
            ])
            ->add('idproject', IntegerType::class, [
                'disabled' => $disabled,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RapportJournalier::class,
            'disabled_fields' => true,
        ]);
    }
}