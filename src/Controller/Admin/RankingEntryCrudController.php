<?php

namespace App\Controller\Admin;

use App\Entity\RankingEntry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RankingEntryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RankingEntry::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ligne de classement')
            ->setEntityLabelInPlural('Classement')
            ->setDefaultSort(['displayOrder' => 'ASC', 'points' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['teamName'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer la ligne'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Équipe et saison')
            ->setHelp("Choisis la saison puis l'équipe à afficher dans le classement.");

        yield AssociationField::new('season', 'Saison')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setHelp('Saison à laquelle rattacher cette ligne.');

        yield TextField::new('teamName', 'Nom de l’équipe')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'équipe",
                'autocomplete' => 'off',
                'placeholder' => 'Exemple : Cécifoot La Bassée',
            ])
            ->setHelp("Nom affiché dans le tableau de classement.");

        yield FormField::addFieldset('Statistiques')
            ->setHelp('Renseigne les chiffres à afficher dans le classement public.');

        yield IntegerField::new('points', 'Points')
            ->setColumns(3)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nombre de points',
                'min' => 0,
                'inputmode' => 'numeric',
            ]);

        yield IntegerField::new('wins', 'Victoires')
            ->setColumns(3)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nombre de victoires',
                'min' => 0,
                'inputmode' => 'numeric',
            ]);

        yield IntegerField::new('losses', 'Défaites')
            ->setColumns(3)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nombre de défaites',
                'min' => 0,
                'inputmode' => 'numeric',
            ]);

        yield IntegerField::new('goalDifference', 'Différence de buts')
            ->setColumns(3)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Différence de buts',
                'inputmode' => 'numeric',
            ]);

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setColumns(3)
            ->setHelp('Laisse 0 si tu veux surtout t’appuyer sur les points.')
            ->setFormTypeOption('attr', [
                'aria-label' => "Ordre d'affichage",
                'min' => 0,
                'inputmode' => 'numeric',
            ]);
    }
}
