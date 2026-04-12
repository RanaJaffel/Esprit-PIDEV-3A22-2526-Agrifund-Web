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
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;

class EvaluationRisqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scoreGlobal', IntegerType::class, [
                'label' => 'Score Global',
                'attr' => ['class' => 'field-input', 'min' => 0, 'max' => 100, 'placeholder' => 'Score entre 0 et 100'],
                'constraints' => [
                    new NotBlank(message: 'Le score est obligatoire'),
                    new Range(min: 0, max: 100, notInRangeMessage: 'Entre {{ min }} et {{ max }}'),
                ],
            ])
            ->add('niveauRisque', ChoiceType::class, [
                'label' => 'Niveau de Risque',
                'placeholder' => '— Choisir un niveau —',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyen' => 'moyen',
                    'Élevé' => 'eleve',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank(message: 'Le niveau de risque est obligatoire')],
            ])
            ->add('fiabiliteDonnees', ChoiceType::class, [
                'label' => 'Fiabilité des Données',
                'placeholder' => '— Choisir la fiabilité —',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyenne' => 'moyenne',
                    'Élevée' => 'elevee',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank(message: 'La fiabilité est obligatoire')],
            ])
            ->add('facteurPrincipal', TextareaType::class, [
                'label' => 'Facteur Principal',
                'attr' => ['class' => 'field-input', 'rows' => 4, 'placeholder' => 'Décrivez le facteur principal...'],
                'constraints' => [
                    new NotBlank(message: 'Le facteur principal est obligatoire'),
                    new Length(min: 10, max: 1000, minMessage: 'Minimum {{ limit }} caractères'),
                ],
            ])
            ->add('recommandation', IntegerType::class, [
                'label' => 'Recommandation',
                'attr' => ['class' => 'field-input', 'min' => 0, 'max' => 100, 'placeholder' => 'Score de recommandation'],
                'constraints' => [
                    new NotBlank(message: 'La recommandation est obligatoire'),
                    new Range(min: 0, max: 100, notInRangeMessage: 'Entre {{ min }} et {{ max }}'),
                ],
            ])
            ->add('dateEvaluation', DateTimeType::class, [
                'label' => 'Date d\'Évaluation',
                'widget' => 'single_text',
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank(message: 'La date est obligatoire')],
            ])
            ->add('projet', EntityType::class, [
                'class' => ProjetAgricole::class,
                'choice_label' => fn(ProjetAgricole $p) => '#'.$p->getIdproject().' — '.$p->getNomproject().' ('.$p->getStatut().')',
                'placeholder' => '— Choisir un projet —',
                'label' => 'Projet Agricole',
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank(message: 'Veuillez choisir un projet')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => EvaluationRisque::class]);
    }
}