<?php

namespace App\Form;

use App\Constant\Constraint;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $event = $builder->getData();
        if ($event instanceof Event && null !== $event->getCity()) {
            $postalCode = $event->getCity()->getPostalCode();
            $cityName = $event->getCity()->getName();
        } else {
            $postalCode = null;
            $cityName = null;
        }

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
            ->add('place', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
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
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
            ])
            ->add('city', CityType::class, [
                'city_name' => $cityName,
                'city_postal_code' => $postalCode,
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
            'data_class' => Event::class,
        ]);
    }
}
