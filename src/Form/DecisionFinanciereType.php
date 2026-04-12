<?php
namespace App\Form;

use App\Entity\DecisionFinanciere;
use App\Entity\EvaluationRisque;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Choice;

class DecisionFinanciereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'placeholder' => '— Choisir un statut —',
                'choices' => [
                    'Approuvé' => 'approuve',
                    'Refusé' => 'refuse',
                    'En attente' => 'en_attente',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir un statut'),
                    new Choice(
                        choices: ['approuve', 'refuse', 'en_attente'],
                        message: 'Statut invalide'
                    ),
                ],
            ])
            ->add('justification', TextareaType::class, [
                'label' => 'Justification',
                'attr' => [
                    'class' => 'field-input',
                    'rows' => 5,
                    'placeholder' => 'Expliquez la raison de cette décision...',
                    'minlength' => 10,
                ],
                'constraints' => [
                    new NotBlank(message: 'La justification est obligatoire'),
                    new Length(
                        min: 10,
                        max: 1000,
                        minMessage: 'La justification doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'La justification ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
            ])
            ->add('evaluation', EntityType::class, [
                'class' => EvaluationRisque::class,
                'placeholder' => '— Choisir une évaluation —',
                'choice_label' => fn(EvaluationRisque $e) => '#'.$e->getIdEvaluation().' — '.$e->getNiveauRisque().' (Score: '.$e->getScoreGlobal().')',
                'label' => 'Évaluation de risque',
                'attr' => ['class' => 'field-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir une évaluation'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DecisionFinanciere::class]);
    }
}