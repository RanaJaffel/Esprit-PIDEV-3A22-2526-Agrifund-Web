<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RapportJournalierGenerateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['for_admin']) {
            $builder->add('idproject', IntegerType::class, [
                'label' => 'ID Projet',
                'required' => true,
            ]);
        }

        $builder->add('date', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date du rapport',
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'for_admin' => false,
        ]);
    }
}