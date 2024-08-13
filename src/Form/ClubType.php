<?php

namespace App\Form;

use App\Constant\Constraint;
use App\Entity\Club;
use App\Entity\Discipline;
use App\Entity\User;
use App\Repository\DisciplineRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;

class ClubType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $club = $builder->getData();
        if ($club instanceof Club && null !== $club->getCity()) {
            $postalCode = $club->getCity()->getPostalCode();
            $cityName = $club->getCity()->getName();
        } else {
            $postalCode = null;
            $cityName = null;
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
            ])
            ->add('addressComplement', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
            ])
            ->add('city', CityType::class, [
                'city_name' => $cityName,
                'city_postal_code' => $postalCode,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site internet',
                'required' => false,
            ])
            ->add('instagram', UrlType::class, [
                'label' => 'Instagram',
                'required' => false,
            ])
            ->add('facebook', UrlType::class, [
                'label' => 'Facebook',
                'required' => false,
            ])
            ->add('youtube', UrlType::class, [
                'label' => 'YouTube',
                'required' => false,
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => Constraint::IMAGE_MAX_FILE_SIZE,
                        'mimeTypes' => Constraint::IMAGE_ALLOWED_MIME_TYPES,
                    ]),
                ],
            ])
            ->add('coverImage', FileType::class, [
                'label' => 'Couverture',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => Constraint::IMAGE_MAX_FILE_SIZE,
                        'mimeTypes' => Constraint::IMAGE_ALLOWED_MIME_TYPES,
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'ckeditor',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
            ])
            ->add('disciplines', EntityType::class, [
                'label' => 'Disciplines associées',
                'class' => Discipline::class,
                'choice_label' => 'label',
                'multiple' => true,
                'query_builder' => function (DisciplineRepository $disciplineRepository): QueryBuilder {
                    return $disciplineRepository->createQueryBuilder('d')->orderBy('d.label', 'ASC');
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre',
            ])
        ;

        if (is_array($options['roles']) && in_array('ROLE_ADMIN', $options['roles'])) {
            if ($club instanceof Club && null !== $club->getId()) {
                $clubAdmin = $this->userRepository->getClubAdmin($club);
                if (!$clubAdmin instanceof User) {
                    $clubAdmin = null;
                }
            } else {
                $clubAdmin = null;
            }

            $builder->add('admin_email', EmailType::class, [
                'label' => 'E-mail de l\'administrateur',
                'required' => false,
                'mapped' => false,
                'help' => 'Cet e-mail sera le contact par défaut pour toute nouvelle inscription.',
                'constraints' => [
                    new Email(),
                ],
                'data' => $clubAdmin ? $clubAdmin->getEmail() : null,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
            'roles' => [],
        ]);
    }
}
