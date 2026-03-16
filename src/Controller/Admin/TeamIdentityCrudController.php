<?php

namespace App\Controller\Admin;

use App\Entity\TeamIdentity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TeamIdentityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TeamIdentity::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Équipe')
            ->setEntityLabelInPlural('Équipes')
            ->setDefaultSort(['teamName' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['teamName', 'aliases'])
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
        yield TextField::new('teamName', "Nom de l'équipe")
            ->setHelp("Nom principal utilisé dans l'administration et sur le site.")
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'équipe",
                'placeholder' => 'Exemple : RC Lens Cécifoot',
            ]);

        yield TextareaField::new('aliases', 'Autres noms reconnus')
            ->setNumOfRows(5)
            ->setHelp("Un nom par ligne. Utile si la même équipe apparaît avec plusieurs variantes.")
            ->setFormTypeOption('attr', [
                'aria-label' => "Autres noms de l'équipe",
                'placeholder' => "Exemple :\nRC Lens Cécifoot\nRCL Cécifoot",
            ])
            ->hideOnIndex();

        yield ImageField::new('logoPath', 'Logo')
            ->setBasePath('uploads/team-identities')
            ->setUploadDir('public/uploads/team-identities')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Logo utilisé automatiquement dans les affichages de matchs et de résultats.");
    }
}
