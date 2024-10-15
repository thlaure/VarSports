<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VarSportsMemberEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('varsportsMembers', EntityType::class, [
                'label' => 'Ajouter/Supprimer des membres VarSports',
                'class' => User::class,
                'choice_label' => function (User $user): string {
                    return $user->getName().' '.$user->getFirstname();
                },
                'multiple' => true,
                'query_builder' => function (UserRepository $userRepository): QueryBuilder {
                    return $userRepository->createQueryBuilder('u')
                        ->andWhere('u.name IS NOT NULL')
                        ->andWhere('u.isVerified = true')
                        ->andWhere('u.isVarsportsMember = true')
                        ->orderBy('u.name', 'ASC')
                        ->orderBy('u.firstname', 'ASC');
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Soumettre',
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
