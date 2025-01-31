<?php

namespace App\Form;

use App\Constant\Constraint;
use App\Entity\Article;
use App\Entity\HomeCategory;
use App\Repository\HomeCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('text', TextareaType::class, [
                'label' => 'Texte',
                'required' => false,
                'attr' => [
                    'class' => 'ckeditor',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'help' => 'Taille maximum : '.Constraint::IMAGE_MAX_FILE_SIZE / 1024 / 1024 .' Mo. Formats acceptés : '.implode(', ', Constraint::IMAGE_ALLOWED_EXTENSIONS),
                'constraints' => [
                    new File([
                        'maxSize' => Constraint::IMAGE_MAX_FILE_SIZE,
                        'mimeTypes' => Constraint::IMAGE_ALLOWED_MIME_TYPES,
                    ]),
                ],
            ])
            ->add('homeCategory', EntityType::class, [
                'label' => 'Catégorie',
                'class' => HomeCategory::class,
                'choice_label' => 'categoryName',
                'choice_value' => 'label',
                'required' => false,
                'query_builder' => fn (HomeCategoryRepository $homeCategoryRepository) => $homeCategoryRepository->createQueryBuilder('c')->orderBy('c.label', 'ASC'),
            ])
            ->add('externalLink', TextType::class, [
                'label' => 'Lien externe',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
