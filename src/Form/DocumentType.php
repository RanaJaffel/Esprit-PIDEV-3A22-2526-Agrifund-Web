<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du document',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Carte d\'identité'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du document est obligatoire']),
                ],
            ])
            ->add('typeDocument', ChoiceType::class, [
                'label' => 'Type de document',
                'choices' => [
                    'Carte d\'identité nationale (CNI)' => 'cni',
                    'Passeport' => 'passeport',
                    'Permis de conduire' => 'permis_conduire',
                    'Certificat bio' => 'certificat_bio',
                    'Titre de propriété' => 'titre_propriete',
                    'Registre de commerce' => 'registre_commerce',
                    'Agrément' => 'agrement',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez le type',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le type de document est obligatoire']),
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration (optionnel)',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier du document',
                'mapped' => false,
                'required' => !$options['data']->getId(), // Obligatoire seulement en création
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier valide (PDF, DOC, DOCX, JPEG, PNG)',
                    ])
                ],
                'help' => 'Formats acceptés: PDF, DOC, DOCX, JPEG, PNG. Taille max: 5 Mo',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}