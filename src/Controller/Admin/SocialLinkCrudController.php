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
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('label', 'Libellé')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nom du réseau social',
            ])
            ->setHelp("Nom affiché dans l'en-tête du site.");
        yield TextField::new('url', 'URL')
            ->setFormTypeOption('attr', [
                'autocomplete' => 'url',
                'aria-label' => 'Adresse du réseau social',
            ])
            ->setHelp('Lien complet vers le profil social.');
        yield TextField::new('icon', 'Icône')
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'icône du réseau social",
            ])
            ->setHelp("Nom technique de l'icône si utilisé par le thème.");
        yield IntegerField::new('displayOrder', 'Ordre')
            ->setHelp("Ordre d'affichage dans l'en-tête.");
        yield BooleanField::new('isVisible', 'Visible')
            ->renderAsSwitch(false)
            ->setHelp("Active l'affichage public du lien social.");
    }
}
