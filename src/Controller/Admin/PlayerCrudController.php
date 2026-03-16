<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Enum\ContentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
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
            ->setSearchFields(['name', 'nationality', 'preferredPosition', 'slug'])
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
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_EDIT, $preview)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Identité du joueur')
            ->renderCollapsed()
            ->setHelp('Renseigne les informations principales du joueur visibles sur sa fiche.');

        yield TextField::new('name', 'Nom complet')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'player',
                'autocomplete' => 'off',
                'aria-label' => 'Nom du joueur',
                'placeholder' => 'Exemple : Youness Guarziz',
            ])
            ->setHelp("Nom affiché dans l'effectif et sur la fiche joueur.");

        yield TextField::new('slug', 'Adresse web')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'player',
                'autocomplete' => 'off',
                'aria-label' => 'Adresse web du joueur',
            ])
            ->setHelp('Générée automatiquement depuis le nom, modifiable si besoin.')
            ->hideOnIndex();

        yield DateField::new('birthDate', 'Date de naissance')
            ->setColumns(4)
            ->setFormat('dd/MM/yyyy')
            ->setHelp("L'âge affiché sur le site est calculé automatiquement à partir de cette date.");

        yield TextField::new('nationality', 'Nationalité')
            ->setColumns(4)
            ->setHelp('Nationalité affichée sur la fiche joueur.');

        yield TextField::new('preferredPosition', 'Poste préférentiel')
            ->setColumns(4)
            ->setHelp('Poste ou rôle de jeu principal.');

        yield TextField::new('preferredFoot', 'Pied préférentiel')
            ->setColumns(4)
            ->setHelp('Exemple : droitier, gaucher, ambidextre.');

        yield IntegerField::new('age', 'Âge')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'readonly' => true,
                'aria-label' => 'Âge du joueur',
            ])
            ->setHelp('Champ calculé automatiquement depuis la date de naissance.')
            ->hideOnForm();

        yield ImageField::new('photo', 'Photo principale')
            ->setColumns(12)
            ->setBasePath('uploads/players')
            ->setUploadDir('public/uploads/players')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Photo affichée sur la carte effectif. Les photos complémentaires se gèrent dans la section Photos joueurs.")
            ->hideOnIndex();

        yield TextareaField::new('description', 'Présentation du joueur')
            ->setColumns(12)
            ->setNumOfRows(10)
            ->setFormTypeOption('attr', [
                'rows' => 8,
                'aria-label' => 'Présentation du joueur',
                'placeholder' => 'Présente le joueur, son profil, ses qualités et sa place dans le collectif.',
            ])
            ->setHelp('Texte principal visible sur la fiche du joueur.');

        yield FormField::addFieldset('Affichage')
            ->renderCollapsed()
            ->setHelp("Définis l'ordre dans l'effectif et la visibilité publique.");

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
                'aria-label' => "Ordre d'affichage du joueur",
            ])
            ->setHelp("Plus le chiffre est petit, plus le joueur remonte dans l'effectif.");

        yield ChoiceField::new('status', 'Visibilité')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publié' => ContentStatus::Published,
                'Archivé' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(8)
            ->setHelp('Brouillon : non visible. Publié : visible. Archivé : conservé.');

        yield FormField::addFieldset('Référencement')
            ->renderCollapsed()
            ->setHelp('Champs facultatifs pour Google et le partage social.')
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Titre SEO')
            ->setColumns(6)
            ->setHelp('Laisse vide pour reprendre automatiquement le nom du joueur.')
            ->hideOnIndex();

        yield TextareaField::new('metaDescription', 'Description SEO')
            ->setColumns(6)
            ->setNumOfRows(3)
            ->setHelp('Laisse vide pour reprendre automatiquement un extrait de la présentation.')
            ->hideOnIndex();
    }
}
