<?php

namespace App\Controller\Admin;

use App\Entity\SocialLink;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SocialLinkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SocialLink::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réseau social')
            ->setEntityLabelInPlural('Réseaux sociaux')
            ->setDefaultSort(['displayOrder' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['label', 'url'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('label', 'Nom affiché')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nom du réseau social',
                'placeholder' => 'Exemple : Facebook',
            ])
            ->setHelp('Nom affiché sur le site.');

        yield TextField::new('url', 'Lien')
            ->setFormTypeOption('attr', [
                'autocomplete' => 'url',
                'aria-label' => 'Adresse du réseau social',
                'placeholder' => 'https://...',
            ])
            ->setHelp('Lien complet vers la page ou le profil social.');

        yield TextField::new('icon', 'Icône')
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'icône du réseau social",
                'placeholder' => 'Exemple : facebook',
            ])
            ->setHelp("Nom technique de l'icône si le thème l'utilise.");

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setHelp('Plus le chiffre est petit, plus le lien remonte.');

        yield BooleanField::new('isVisible', 'Visible sur le site')
            ->renderAsSwitch(false)
            ->setHelp('Active ou masque ce lien sur le site public.');
    }
}
