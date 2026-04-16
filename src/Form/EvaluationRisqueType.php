<?php

namespace App\Form;

use App\Entity\EvaluationRisque;
use App\Entity\ProjetAgricole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class EvaluationRisqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scoreGlobal', IntegerType::class, [
                'label' => 'Score Global',
                'attr' => ['class' => 'field-input', 'readonly' => true],
                'constraints' => [new NotBlank(), new \Symfony\Component\Validator\Constraints\Range(min: 0, max: 100)],
            ])
            ->add('niveauRisque', ChoiceType::class, [
                'label' => 'Niveau de Risque',
                'placeholder' => '— Choisir un niveau —',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyen'  => 'moyen',
                    'Élevé'  => 'eleve',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank()],
            ])
            ->add('fiabiliteDonnees', ChoiceType::class, [
                'label' => 'Fiabilité des Données',
                'placeholder' => '— Choisir la fiabilité —',
                'choices' => [
                    'Faible'   => 'faible',
                    'Moyenne'  => 'moyenne',
                    'Élevée'   => 'elevee',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank()],
            ])
            ->add('recommandation', TextareaType::class, [   // ← CHANGÉ ICI
                'label' => 'Recommandation',
                'attr' => [
                    'class' => 'field-input',
                    'rows' => 3,
                    'readonly' => true,
                    'placeholder' => 'Recommandation IA apparaîtra ici...'
                ],
                'constraints' => [
                    new NotBlank(message: 'La recommandation est obligatoire'),
                    new Length(max: 500),
                ],
            ])
            ->add('facteurPrincipal', TextareaType::class, [
                'label' => 'Analyse Détaillée & Conseils',
                'attr' => ['class' => 'field-input', 'rows' => 10, 'readonly' => true],
                'constraints' => [new NotBlank(), new Length(min: 10, max: 2000)],
            ])
            ->add('dateEvaluation', DateTimeType::class, [
                'label' => 'Date d\'Évaluation',
                'widget' => 'single_text',
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank()],
            ])
            ->add('projet', EntityType::class, [
                'class' => ProjetAgricole::class,
                'choice_label' => fn(ProjetAgricole $p) => '#'.$p->getIdproject().' — '.$p->getNomproject().' ('.$p->getStatut().')',
                'placeholder' => '— Choisir un projet —',
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => EvaluationRisque::class]);
    }
}