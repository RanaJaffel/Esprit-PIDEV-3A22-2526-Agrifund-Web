<?php

namespace App\Form;

use App\Entity\ProjectAgricole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;


class ProjectAgricoleAgriculteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = new \DateTime('today');

        $builder
            ->add('nomproject', TextType::class, [
                'label' => 'Nom du projet',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Projet Oliviers Nabeul',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du projet est obligatoire.']),
                    new Length([
                        'min'        => 3,
                        'max'        => 150,
                        'minMessage' => 'Le nom doit comporter au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (hectares)',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 25.5',
                    'step'        => '0.01',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La surface est obligatoire.']),
                    new Positive(['message' => 'La surface doit être un nombre positif.']),
                    // Only max → maxMessage allowed
                    new Range([
                        'max'        => 100000,
                        'maxMessage' => 'La surface ne peut pas dépasser {{ limit }} hectares.',
                    ]),
                ],
            ])
            ->add('budgetdemande', NumberType::class, [
                'label' => 'Budget demandé (TND)',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 50000.00',
                    'step'        => '0.01',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le budget est obligatoire.']),
                    new Positive(['message' => 'Le budget doit être un montant positif.']),
                    // Only max → maxMessage allowed
                    new Range([
                        'max'        => 999999999,
                        'maxMessage' => 'Le budget ne peut pas dépasser {{ limit }} TND.',
                    ]),
                ],
            ])
            ->add('datesoumission', DateType::class, [
                'label'  => 'Date de soumission',
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'La date de soumission est obligatoire.']),
                    new GreaterThanOrEqual([
                        'value'   => $today,
                        'message' => 'La date de soumission ne peut pas être dans le passé.',
                    ]),
                    new LessThanOrEqual([
                        'value'   => $today,
                        'message' => 'La date de soumission ne peut pas être dans le futur.',
                    ]),
                ],
            ])
            ->add('latitude', NumberType::class, [
                'label'    => 'Latitude (optionnel)',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 36.8065',
                    'step'        => 'any',
                ],
                'constraints' => [
                    
                    new Range([
                        'min'               => -90,
                        'max'               => 90,
                        'notInRangeMessage' => 'La latitude doit être comprise entre {{ min }}° et {{ max }}°.',
                    ]),
                ],
            ])
            ->add('longitude', NumberType::class, [
                'label'    => 'Longitude (optionnel)',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 10.1815',
                    'step'        => 'any',
                ],
                'constraints' => [
                    
                    new Range([
                        'min'               => -180,
                        'max'               => 180,
                        'notInRangeMessage' => 'La longitude doit être comprise entre {{ min }}° et {{ max }}°.',
                    ]),
                ],
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProjectAgricole::class]);
    }
}