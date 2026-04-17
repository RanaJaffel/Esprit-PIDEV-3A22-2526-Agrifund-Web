<?php

namespace App\Form;

use App\Entity\DecisionFinanciere;
use App\Entity\EvaluationRisque;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
                    'Refusé'   => 'refuse',
                ],
                'attr' => ['class' => 'field-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir un statut'),
                    new Choice(choices: ['approuve', 'refuse']),
                ],
            ])

            ->add('evaluation', EntityType::class, [
                'class' => EvaluationRisque::class,
                'label' => 'Évaluation liée',
                'placeholder' => '— Choisir une évaluation —',
                'choice_label' => function(EvaluationRisque $e) {
                    $reco = $e->getRecommandation() 
                        ? ' — ' . mb_substr($e->getRecommandation(), 0, 65) . '...' 
                        : '';
                    return sprintf('#%d — %s (Score: %d)%s',
                        $e->getIdEvaluation(),
                        ucfirst($e->getNiveauRisqueNormalized()),
                        $e->getScoreGlobal(),
                        $reco
                    );
                },
                'attr' => ['class' => 'field-input'],
                'constraints' => [new NotBlank()],
                'query_builder' => fn($er) => $er->createQueryBuilder('e')->orderBy('e.idEvaluation', 'DESC'),
            ])

            ->add('dateDecision', DateTimeType::class, [   // ← Ajout de la date
                'label' => 'Date de décision',
                'widget' => 'single_text',
                'attr' => ['class' => 'field-input'],
                'data' => new \DateTime(),   // Date d'aujourd'hui par défaut
                'constraints' => [new NotBlank()],
            ])

            ->add('justification', TextareaType::class, [
                'label' => 'Justification',
                'attr' => [
                    'class' => 'field-input',
                    'rows' => 5,
                    'placeholder' => 'Expliquez la raison de cette décision...',
                ],
                'constraints' => [
                    new NotBlank(message: 'La justification est obligatoire'),
                    new Length(min: 10, max: 1000),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DecisionFinanciere::class]);
    }
}