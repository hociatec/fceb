<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Enum\ContentStatus;
use App\Enum\PagePlacement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->setDefaultSort(['menuOrder' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['title', 'slug'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $preview = Action::new('preview', 'Prévisualiser')
            ->linkToRoute('site_page', static fn (Page $page): array => [
                'slug' => $page->getSlug(),
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
        yield FormField::addFieldset('Contenu de la page')
            ->renderCollapsed()
            ->setHelp('Crée ou modifie une page d’information visible sur le site.');

        yield TextField::new('title', 'Titre')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Titre de la page',
                'placeholder' => 'Exemple : Découvrir le cécifoot',
            ])
            ->setHelp('Titre visible dans la page et éventuellement dans la navigation.');

        yield TextField::new('slug', 'Adresse web')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Adresse web de la page',
            ])
            ->setHelp('Générée automatiquement depuis le titre, modifiable si besoin.')
            ->hideOnIndex();

        yield ImageField::new('heroImage', "Image d'en-tête")
            ->setColumns(12)
            ->setBasePath('uploads/pages')
            ->setUploadDir('public/uploads/pages')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Image illustrant la page.")
            ->hideOnIndex();

        yield TextEditorField::new('content', 'Contenu complet')
            ->setColumns(12)
            ->setNumOfRows(18)
            ->setTrixEditorConfig([
                'blockAttributes' => [
                    'default' => ['tagName' => 'p'],
                    'heading1' => ['tagName' => 'h2'],
                    'heading2' => ['tagName' => 'h3'],
                ],
            ])
            ->setFormTypeOption('attr', [
                'aria-label' => 'Contenu de la page',
                'data-page-rich-editor' => '1',
            ])
            ->setHelp('Utilise les boutons du haut pour structurer la page.')
            ->hideOnIndex();

        yield FormField::addFieldset('Affichage')
            ->renderCollapsed()
            ->setHelp('Définis où la page apparaît et dans quel ordre.');

        yield ChoiceField::new('placement', 'Où afficher cette page')
            ->setChoices([
                'Nulle part dans le menu' => PagePlacement::None,
                'Menu du haut' => PagePlacement::Header,
                'Pied de page' => PagePlacement::Footer,
                'Menu du haut + pied de page' => PagePlacement::Both,
            ])
            ->renderExpanded()
            ->setColumns(8)
            ->setHelp('Choisis si cette page doit être accessible depuis la navigation.');

        yield IntegerField::new('menuOrder', "Ordre d'affichage")
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
                'aria-label' => "Ordre d'affichage",
            ])
            ->setHelp('Plus le chiffre est petit, plus la page remonte.');

        yield ChoiceField::new('status', 'Visibilité')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publiée' => ContentStatus::Published,
                'Archivée' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp('Brouillon : non visible. Publiée : visible. Archivée : conservée sans être mise en avant.');

        yield FormField::addFieldset('Référencement')
            ->renderCollapsed()
            ->setHelp('Champs facultatifs pour Google et le partage social.')
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Titre SEO')
            ->setColumns(6)
            ->setHelp('Laisse vide pour reprendre automatiquement le titre de la page.')
            ->hideOnIndex();

        yield TextField::new('metaDescription', 'Description SEO')
            ->setColumns(6)
            ->setHelp('Laisse vide pour reprendre automatiquement un extrait du contenu.')
            ->hideOnIndex();
    }
}
