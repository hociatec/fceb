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
        yield TextField::new('teamName', "Nom de l'équipe")
            ->setHelp("Nom principal affiché dans l'administration et utilisé comme référence de l'équipe.")
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'équipe",
            ]);

        yield TextareaField::new('aliases', 'Alias de nom')
            ->setNumOfRows(5)
            ->setHelp("Un alias par ligne. Utilise ce champ pour gérer les variantes de nom sans créer plusieurs équipes visibles.")
            ->setFormTypeOption('attr', [
                'aria-label' => "Alias de l'équipe",
                'placeholder' => "Exemple :\nRC Lens Cécifoot\nRCL Cécifoot",
            ])
            ->hideOnIndex();

        yield ImageField::new('logoPath', 'Logo')
            ->setBasePath('uploads/team-identities')
            ->setUploadDir('public/uploads/team-identities')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Logo administrable depuis le back-office. Il remplace le logo défini en dur.");
    }
}
