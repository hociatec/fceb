<?php

namespace App\Controller\Admin;

use App\Entity\ClubSettings;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClubSettingsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClubSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paramètres du club')
            ->setEntityLabelInPlural('Paramètres du club')
            ->setPaginatorPageSize(1)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::DELETE, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer les paramètres'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations du club')
            ->setHelp('Ces informations sont réutilisées dans le site public, le footer et les formulaires.');

        yield TextField::new('clubName', 'Nom du club')->setColumns(6);
        yield TextField::new('publicEmail', 'E-mail public')->setColumns(6);
        yield TextField::new('phone', 'Téléphone')->setColumns(6);
        yield TextField::new('address', 'Adresse')->setColumns(6);
        yield TextField::new('mapUrl', 'Lien carte')->setColumns(12);

        yield FormField::addFieldset("Page d'accueil")
            ->setHelp("Personnalise les grands textes structurels de la page d'accueil sans toucher au code.");

        yield TextField::new('homeIntroTitle', 'Titre du bloc club')->setColumns(6);
        yield TextField::new('homeIntroSubtitle', 'Sous-titre du bloc club')->setColumns(6);
        yield TextareaField::new('homeIntroLead', 'Texte principal du bloc club')->setColumns(12)->setNumOfRows(5);
        yield TextareaField::new('homeIntroMediaNote', 'Note média du bloc club')->setColumns(12)->setNumOfRows(3);
        yield TextField::new('homeFeaturedTitle', 'Titre actu à la une')->setColumns(6);
        yield TextField::new('homeFeaturedSubtitle', 'Sous-titre actu à la une')->setColumns(6);
        yield TextField::new('homeUpcomingTitle', 'Titre prochain match')->setColumns(6);
        yield TextField::new('homeUpcomingSubtitle', 'Sous-titre prochain match')->setColumns(6);
        yield TextField::new('homeLastResultTitle', 'Titre dernier résultat')->setColumns(6);
        yield TextField::new('homeLastResultSubtitle', 'Sous-titre dernier résultat')->setColumns(6);
    }
}
