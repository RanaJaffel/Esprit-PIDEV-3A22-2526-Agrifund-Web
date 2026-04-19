<?php
// src/Form/Parametres2faType.php

namespace App\Form;

use App\Entity\Parametres2fa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Parametres2faType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estActive', CheckboxType::class, [
                'label' => 'Activer l\'authentification à deux facteurs',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'id' => 'toggle2FA'
                ],
                'label_attr' => ['class' => 'form-check-label']
            ])
            ->add('methodePreferee', ChoiceType::class, [
                'label' => 'Méthode de vérification',
                'choices' => [
                    'Email' => 'email',
                    'SMS' => 'sms',
                ],
                'expanded' => true,
                'attr' => ['class' => 'method-choice'],
                'label_attr' => ['class' => 'form-label fw-bold']
            ])
            ->add('telephone2fa', TelType::class, [
                'label' => 'Numéro de téléphone (pour SMS)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+216 XX XXX XXX',
                    'id' => 'phone2fa'
                ],
                'label_attr' => ['class' => 'form-label']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parametres2fa::class,
        ]);
    }
}