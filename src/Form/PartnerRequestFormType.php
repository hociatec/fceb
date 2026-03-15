<?php

namespace App\Form;

use App\Form\Model\PartnerRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnerRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organization', TextType::class, [
                'label' => 'Structure',
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Nom du contact',
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
            ->add('supportType', ChoiceType::class, [
                'label' => 'Type de partenariat envisagé',
                'choices' => [
                    'Soutien financier' => 'financier',
                    'Visibilité / communication' => 'visibilite',
                    'Mise à disposition de matériel' => 'materiel',
                    'Partenariat événementiel' => 'evenementiel',
                    'Échange à définir' => 'autre',
                ],
                'placeholder' => 'Choisir une option',
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site web',
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['rows' => 6],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PartnerRequestData::class,
        ]);
    }
}
