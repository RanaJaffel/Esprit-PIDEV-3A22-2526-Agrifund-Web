<?php

namespace App\Form;

use App\Entity\OffreFinanciere;
use App\Entity\ProduitFinancier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreFinanciereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOffre', TextType::class, [
                'label' => 'Offer Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Spring Offer 2026'],
            ])
            ->add('conditions', TextareaType::class, [
                'label' => 'Conditions',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter offer conditions...',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Active' => 'Active',
                    'Pending' => 'En attente',
                    'Cancelled' => 'Cancelled',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('produitFinancier', EntityType::class, [
                'label' => 'Financial Product',
                'class' => ProduitFinancier::class,
                'choice_label' => 'nomProduit',
                'placeholder' => 'Select a product',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Offer Price (TND)',
                'required' => true,
                'currency' => 'TND',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => '0.01',
                    'step' => '0.01',
                    'placeholder' => 'e.g., 4999.90',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreFinanciere::class,
        ]);
    }
}
