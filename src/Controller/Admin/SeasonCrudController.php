<?php

namespace App\Controller\Admin;

use App\Entity\Season;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SeasonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Season::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Saison')
            ->setEntityLabelInPlural('Saisons')
            ->setDefaultSort(['startDate' => 'DESC'])
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
        yield TextField::new('name', 'Nom')
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'season',
                'autocomplete' => 'off',
                'aria-label' => 'Nom de la saison',
            ])
            ->setHelp('Exemple : Saison 2026-2027.');

        yield TextField::new('slug', 'Slug')
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'season',
                'autocomplete' => 'off',
                'aria-label' => 'Slug de la saison',
            ])
            ->setHelp('Rempli automatiquement depuis le nom, modifiable si besoin.')
            ->hideOnIndex();

        yield DateField::new('startDate', 'Début de saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date de début de la saison',
            ])
            ->setHelp('Date de début officielle de la saison.');

        yield DateField::new('endDate', 'Fin de saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date de fin de la saison',
            ])
            ->setHelp('Date de fin officielle de la saison.');

        yield BooleanField::new('isCurrent', 'Saison active')
            ->renderAsSwitch(false)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Définir cette saison comme saison en cours',
            ])
            ->setHelp('À activer uniquement pour la saison actuellement affichée sur le site.');
    }
}
