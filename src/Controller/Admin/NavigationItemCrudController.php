<?php

namespace App\Controller\Admin;

use App\Entity\NavigationItem;
use App\Enum\NavigationItemLocation;
use App\Enum\NavigationItemType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NavigationItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NavigationItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Lien de navigation')
            ->setEntityLabelInPlural('Navigation')
            ->setDefaultSort(['location' => 'ASC', 'displayOrder' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['label', 'routeName', 'externalUrl', 'page.title'])
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
        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('location', 'Zone')
                ->setChoices([
                    'Header' => NavigationItemLocation::Header,
                    'Footer' => NavigationItemLocation::Footer,
                ]);
            yield TextField::new('label', 'Libellé');
            yield ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Route interne' => NavigationItemType::Route,
                    'Page du site' => NavigationItemType::Page,
                    'URL externe' => NavigationItemType::Url,
                ]);
            yield IntegerField::new('displayOrder', 'Ordre');
            yield BooleanField::new('isEnabled', 'Visible');

            return;
        }

        yield FormField::addFieldset('Lien')
            ->renderCollapsed()
            ->setHelp('Chaque lien du header et du footer se gère ici, sans logique cachée dans les templates.');

        yield ChoiceField::new('location', 'Zone')
            ->setChoices([
                'Header' => NavigationItemLocation::Header,
                'Footer' => NavigationItemLocation::Footer,
            ])
            ->renderExpanded()
            ->setColumns(6);

        yield TextField::new('label', 'Libellé')
            ->setColumns(6);

        yield ChoiceField::new('type', 'Type de cible')
            ->setChoices([
                'Route interne' => NavigationItemType::Route,
                'Page du site' => NavigationItemType::Page,
                'URL externe' => NavigationItemType::Url,
            ])
            ->renderExpanded()
            ->setColumns(12);

        yield ChoiceField::new('routeName', 'Route')
            ->setChoices(NavigationItem::availableRouteChoices())
            ->renderAsNativeWidget()
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('Utilisé quand le type est « Route interne ».');

        yield AssociationField::new('page', 'Page')
            ->renderAsNativeWidget()
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('Utilisé quand le type est « Page du site ».');

        yield TextField::new('externalUrl', 'URL externe')
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp('Utilisé quand le type est « URL externe ».');

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
            ]);

        yield BooleanField::new('isEnabled', 'Visible')
            ->renderAsSwitch(false)
            ->setColumns(4);

        yield BooleanField::new('openInNewTab', 'Nouvel onglet')
            ->renderAsSwitch(false)
            ->setColumns(4);
    }
}
