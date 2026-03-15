<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Enum\ContentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlayerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Player::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Joueur')
            ->setEntityLabelInPlural('Effectif')
            ->setDefaultSort(['displayOrder' => 'ASC', 'name' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $preview = Action::new('preview', 'Prévisualiser')
            ->linkToRoute('site_player_show', static fn (Player $player): array => [
                'slug' => $player->getSlug(),
                'preview' => 1,
            ])
            ->setIcon('fa fa-eye')
            ->setCssClass('btn btn-secondary');

        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_EDIT, $preview)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité du joueur')
            ->setHelp('Ajoute le nom, la photo et les informations de présentation du joueur.');

        yield TextField::new('name', 'Nom')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'player',
                'autocomplete' => 'off',
                'aria-label' => 'Nom du joueur',
                'placeholder' => 'Exemple : Romain Dupont',
            ])
            ->setHelp("Nom affiché dans l'effectif et sur la fiche joueur.");

        yield TextField::new('slug', 'Slug')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'player',
                'autocomplete' => 'off',
                'aria-label' => 'Slug du joueur',
            ])
            ->setHelp('Rempli automatiquement depuis le nom, modifiable si besoin.')
            ->hideOnIndex();

        yield ImageField::new('photo', 'Photo')
            ->setColumns(12)
            ->setBasePath('uploads/players')
            ->setUploadDir('public/uploads/players')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Photo du joueur, utilisée dans la page effectif et la fiche détaillée.")
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Meta title')
            ->setColumns(6)
            ->setHelp('Titre SEO facultatif. Si vide, le nom du joueur est utilisé.')
            ->hideOnIndex();

        yield TextareaField::new('metaDescription', 'Meta description')
            ->setColumns(6)
            ->setNumOfRows(3)
            ->setHelp('Description SEO facultative. Si vide, une version courte de la présentation est utilisée.')
            ->hideOnIndex();

        yield TextareaField::new('description', 'Description')
            ->setColumns(12)
            ->setNumOfRows(10)
            ->setFormTypeOption('attr', [
                'rows' => 8,
                'aria-label' => 'Description du joueur',
                'placeholder' => 'Présente le joueur, son parcours, ses qualités et sa place dans le collectif.',
            ])
            ->setHelp('Ce texte sert à présenter le joueur sur sa fiche détaillée.');

        yield FormField::addFieldset('Affichage')
            ->setHelp("Définis l'ordre d'apparition, la visibilité publique et le statut du joueur.");

        yield IntegerField::new('age', 'Âge')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 1,
                'inputmode' => 'numeric',
                'aria-label' => 'Âge du joueur',
            ])
            ->setHelp('Âge affiché sur la carte du joueur et dans sa fiche.')
            ->hideOnIndex();

        yield IntegerField::new('displayOrder', 'Ordre')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
                'aria-label' => "Ordre d'affichage du joueur",
            ])
            ->setHelp("Plus le nombre est petit, plus le joueur remonte dans l'effectif.");

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publié' => ContentStatus::Published,
                'Archivé' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(4)
            ->setHelp('Définit si le joueur est en brouillon, visible publiquement ou archivé.');
    }
}
