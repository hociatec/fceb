<?php

namespace App\Form;

use App\Form\Model\TrialRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrialRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['autocomplete' => 'tel'],
            ])
            ->add('profile', ChoiceType::class, [
                'label' => 'Je souhaite venir en tant que',
                'choices' => [
                    'Joueur ou joueuse' => 'joueur',
                    'Parent ou proche' => 'proche',
                    'Bénévole' => 'benevole',
                    'Encadrant / éducateur' => 'encadrant',
                    'Simple découverte du club' => 'decouverte',
                ],
                'placeholder' => 'Choisir un profil',
            ])
            ->add('availability', TextType::class, [
                'label' => 'Disponibilités ou créneau souhaité',
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
                'attr' => ['rows' => 6],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrialRequestData::class,
        ]);
    }
}
