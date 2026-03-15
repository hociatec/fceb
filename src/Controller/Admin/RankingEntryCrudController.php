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
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer la ligne'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer les modifications'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Équipe et saison')
            ->setHelp("Rattache l'équipe à une saison puis renseigne ses statistiques.");

        yield AssociationField::new('season', 'Saison')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setHelp('Saison à laquelle rattacher cette ligne de classement.');

        yield TextField::new('teamName', 'Équipe')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'équipe",
                'autocomplete' => 'off',
                'placeholder' => 'Exemple : Cécifoot La Bassée',
            ])
            ->setHelp("Nom de l'équipe affiché dans le classement.");

        yield FormField::addFieldset('Statistiques')
            ->setHelp('Renseigne les chiffres qui doivent apparaître dans le tableau public.');

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

        yield IntegerField::new('displayOrder', 'Ordre')
            ->setColumns(3)
            ->setHelp("Ordre d'affichage manuel. Laisse 0 pour suivre le tri automatique.")
            ->setFormTypeOption('attr', [
                'aria-label' => "Ordre d'affichage",
                'min' => 0,
                'inputmode' => 'numeric',
            ]);
    }
}
