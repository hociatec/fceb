<?php

namespace App\Controller\Admin;

use App\Entity\HomeSection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HomeSectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HomeSection::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Section d'accueil")
            ->setEntityLabelInPlural('Accueil')
            ->setDefaultSort(['displayOrder' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['title', 'subtitle', 'sectionKey'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer la section'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset("Configuration de l'accueil")
            ->setHelp("Active, renomme et réordonne les blocs affichés sur la page d'accueil.");

        yield ChoiceField::new('sectionKey', 'Bloc concerné')
            ->setChoices(HomeSection::availableSectionChoices())
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setHelp("Choisis quel bloc de l'accueil tu modifies.");

        yield TextField::new('title', 'Titre affiché')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Titre de la section',
                'placeholder' => "Titre affiché sur l'accueil",
            ])
            ->setHelp('Titre visible au-dessus du bloc.');

        yield TextField::new('subtitle', 'Sous-titre affiché')
            ->setColumns(9)
            ->setRequired(false)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Sous-titre de la section',
                'placeholder' => 'Phrase courte sous le titre',
            ])
            ->setHelp("Petit texte d'accompagnement sous le titre.");

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setColumns(3)
            ->setHelp('Les blocs sont affichés du plus petit nombre au plus grand.')
            ->setFormTypeOption('attr', [
                'aria-label' => "Ordre d'affichage",
                'min' => 0,
                'inputmode' => 'numeric',
            ]);

        yield BooleanField::new('isEnabled', 'Bloc visible')
            ->renderAsSwitch(false)
            ->setColumns(12)
            ->setHelp("Décoche pour masquer ce bloc de l'accueil sans le supprimer.");
    }
}
