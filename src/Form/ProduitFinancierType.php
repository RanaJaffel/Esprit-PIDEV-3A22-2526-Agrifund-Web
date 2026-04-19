<?php

namespace App\Form;

use App\Entity\ProduitFinancier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitFinancierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomProduit', TextType::class, [
                'label' => 'Product Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Credit Equipment'],
            ])
            ->add('typeFinancement', ChoiceType::class, [
                'label' => 'Financing Type',
                'choices' => [
                    'Credit' => 'Crédit',
                    'Loan' => 'Prêt',
                    'Leasing' => 'Leasing',
                    'Subsidy' => 'Subvention',
                    'Microfinance' => 'Microfinance',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('tauxInteret', NumberType::class, [
                'label' => 'Interest Rate (%)',
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., 7.50'],
            ])
            ->add('montant', MoneyType::class, [
                'label' => 'Amount (DT)',
                'currency' => 'TND',
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 50000',
                ],
            ])
            ->add('prixFixe', MoneyType::class, [
                'label' => 'Fixed Payable Price (DT)',
                'currency' => 'TND',
                'input' => 'string',
                'scale' => 2,
                'invalid_message' => 'Le prix fixe doit contenir au maximum 2 décimales.',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 150.00',
                    'min' => '0.01',
                    'step' => '0.01',
                    'inputmode' => 'decimal',
                ],
            ])
            ->add('reglesFinancieres', TextareaType::class, [
                'label' => 'Financial Rules',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Enter financial rules and conditions...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProduitFinancier::class,
        ]);
    }
}
