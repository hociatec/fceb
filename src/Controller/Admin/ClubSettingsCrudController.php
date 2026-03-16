<?php

namespace App\Controller\Admin;

use App\Entity\ClubSettings;
use App\Repository\ClubSettingsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClubSettingsCrudController extends AbstractCrudController
{
    public function __construct(private readonly ClubSettingsRepository $clubSettingsRepository)
    {
    }

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
        $actions = $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer les paramètres'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer les paramètres'));

        if ($this->clubSettingsRepository->count([]) > 0) {
            $actions = $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations du club')
            ->renderCollapsed()
            ->setHelp('Ces informations servent aux coordonnées, au footer et aux formulaires. Le contenu de la page d’accueil se gère désormais dans Blocs d’accueil.');

        yield TextField::new('clubName', 'Nom du club')->setColumns(6);
        yield TextField::new('publicEmail', 'E-mail public')->setColumns(6);
        yield TextField::new('phone', 'Téléphone')->setColumns(6);
        yield TextField::new('address', 'Adresse')->setColumns(6);
        yield TextField::new('mapUrl', 'Lien carte')->setColumns(12);
    }
}
