<?php

namespace App\Controller\Admin;

use App\Entity\TournifySyncRun;
use App\Enum\TournifySyncStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TournifySyncRunCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TournifySyncRun::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Journal Tournify')
            ->setEntityLabelInPlural('Journal Tournify')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action) => $action->setLabel('Voir'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Exécution')
            ->renderCollapsed()
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', 'Lancé le');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Prévisualisation' => TournifySyncStatus::Preview,
                'Succès' => TournifySyncStatus::Success,
                'Erreur' => TournifySyncStatus::Failure,
            ])
            ->renderAsBadges([
                TournifySyncStatus::Preview->value => 'warning',
                TournifySyncStatus::Success->value => 'success',
                TournifySyncStatus::Failure->value => 'danger',
            ]);
        yield BooleanField::new('isDryRun', 'Prévisualisation');
        yield TextField::new('season', 'Saison')->formatValue(static fn ($value, ?TournifySyncRun $run): string => $run?->getSeason()?->getName() ?? 'Aucune');
        yield IntegerField::new('sourceMatches', 'Matchs source');
        yield IntegerField::new('createdCount', 'Créés');
        yield IntegerField::new('updatedCount', 'Mis à jour');
        yield IntegerField::new('removedCount', 'Supprimés');
        yield TextField::new('competition', 'Compétition')->hideOnIndex();
        yield TextField::new('teamName', 'Équipe')->hideOnIndex();
        yield TextField::new('liveLink', 'Live link')->hideOnIndex();
        yield TextField::new('divisionId', 'Division')->hideOnIndex();
        yield TextareaField::new('message', 'Message')->hideOnIndex();
    }
}
