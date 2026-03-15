<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PartnerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Partner::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Partenaire')
            ->setEntityLabelInPlural('Partenaires')
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
        yield TextField::new('name', 'Nom')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nom du partenaire',
            ])
            ->setHelp('Nom affiche sur la page partenaires.');

        yield TextField::new('websiteUrl', 'Site web')
            ->setFormTypeOption('attr', [
                'autocomplete' => 'url',
                'aria-label' => 'Adresse du site web du partenaire',
            ])
            ->setHelp('Lien externe du partenaire.');

        yield ImageField::new('logoUrl', 'Logo')
            ->setBasePath('uploads/partners')
            ->setUploadDir('public/uploads/partners')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Logo du partenaire televerse depuis l'administration.");

        yield IntegerField::new('displayOrder', 'Ordre')
            ->setHelp("Ordre d'affichage dans la liste.");

        yield BooleanField::new('isVisible', 'Visible')
            ->renderAsSwitch(false)
            ->setHelp("Active l'affichage public du partenaire.");
    }
}
