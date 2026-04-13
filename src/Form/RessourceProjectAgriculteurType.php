<?php

namespace App\Form;

use App\Entity\RessourceProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Form for the AGRICULTEUR side.
 * The project dropdown is limited to ONLY this agriculteur's own projects.
 */
class RessourceProjectAgriculteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today      = new \DateTime('today');
        $myProjects = $options['projects'];

        $builder
            ->add('nomressource', TextType::class, [
                'label' => 'Nom de la ressource',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Tracteur John Deere',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la ressource est obligatoire.']),
                    new Length([
                        'min'        => 3,
                        'max'        => 150,
                        'minMessage' => 'Le nom doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('typeressource', ChoiceType::class, [
                'label'       => 'Type de ressource',
                'placeholder' => '-- Choisir un type --',
                'attr'        => ['class' => 'form-select'],
                'choices'     => [
                    'Équipement' => 'equipement',
                    'Matériaux'  => 'materiaux',
                    'Service'    => 'service',
                ],
                'empty_data'  => '',
                'constraints' => [
                    new NotBlank(['message' => 'Le type de ressource est obligatoire.']),
                ],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr'  => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire.']),
                    new Positive(['message' => 'La quantité doit être un entier positif.']),
                    new Range([
                        'max'        => 100000,
                        'maxMessage' => 'La quantité ne peut pas dépasser {{ limit }}.',
                    ]),
                ],
            ])
            ->add('cout', NumberType::class, [
                'label' => 'Coût unitaire (TND)',
                'attr'  => ['class' => 'form-control', 'step' => '0.01'],
                'constraints' => [
                    new NotBlank(['message' => 'Le coût unitaire est obligatoire.']),
                    new Positive(['message' => 'Le coût doit être un montant positif.']),
                    new Range([
                        'max'        => 999999999,
                        'maxMessage' => 'Le coût ne peut pas dépasser {{ limit }} TND.',
                    ]),
                ],
            ])
            ->add('fournisseur', TextType::class, [
                'label'    => 'Fournisseur (optionnel)',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Agri-Tunisie SARL',
                ],
                'constraints' => [
                    new Length([
                        'max'        => 150,
                        'maxMessage' => 'Le fournisseur ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label'       => 'Statut',
                'placeholder' => '-- Choisir un statut --',
                'attr'        => ['class' => 'form-select'],
                'choices'     => [
                    'Prévu'  => 'prevu',
                    'Acheté' => 'achete',
                ],
                'empty_data'  => '',   // prevents null → string TypeError
                'constraints' => [
                    new NotBlank(['message' => 'Le statut est obligatoire.']),
                ],
            ])
            ->add('dateajout', DateType::class, [
                'label'  => "Date d'ajout",
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => "La date d'ajout est obligatoire."]),
                    new GreaterThanOrEqual([
                        'value'   => $today,
                        'message' => "La date d'ajout ne peut pas être dans le passé.",
                    ]),
                    new LessThanOrEqual([
                        'value'   => $today,
                        'message' => "La date d'ajout ne peut pas être dans le futur.",
                    ]),
                ],
            ])
            ->add('project', ChoiceType::class, [
                'label'        => 'Mon projet',
                'placeholder'  => '-- Choisir un projet --',
                'attr'         => ['class' => 'form-select'],
                'choices'      => array_combine(
                    array_map(fn($p) => $p->getNomproject(), $myProjects),
                    $myProjects
                ),
                'choice_label' => fn($p) => $p->getNomproject(),
                'constraints'  => [
                    new NotNull(['message' => 'Le projet agricole est obligatoire.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RessourceProject::class,
            'projects'   => [],
        ]);
    }
}