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
            ->setSearchFields(['name', 'websiteUrl'])
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
        yield TextField::new('name', 'Nom du partenaire')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Nom du partenaire',
                'placeholder' => 'Exemple : Ville de La Bassée',
            ])
            ->setHelp('Nom affiché sur la page partenaires.');

        yield TextField::new('websiteUrl', 'Lien du site')
            ->setFormTypeOption('attr', [
                'autocomplete' => 'url',
                'aria-label' => 'Adresse du site du partenaire',
                'placeholder' => 'https://...',
            ])
            ->setHelp('Lien complet vers le site du partenaire.');

        yield ImageField::new('logoUrl', 'Logo')
            ->setBasePath('uploads/partners')
            ->setUploadDir('public/uploads/partners')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Logo du partenaire importé depuis l'administration.");

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setHelp('Plus le chiffre est petit, plus le partenaire remonte.');

        yield BooleanField::new('isVisible', 'Visible sur le site')
            ->renderAsSwitch(false)
            ->setHelp('Active ou masque le partenaire sur le site public.');
    }
}
