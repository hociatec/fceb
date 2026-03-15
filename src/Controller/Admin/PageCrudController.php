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
            ->disable(Action::BATCH_DELETE, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_EDIT, $preview)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Contenu de la page')
            ->setHelp('Crée une page statique et définis sa place dans la navigation.');

        yield TextField::new('title', 'Titre')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Titre de la page',
                'placeholder' => 'Exemple : Discipline',
            ])
            ->setHelp('Titre visible dans le menu et sur la page.');

        yield TextField::new('slug', 'Slug')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Slug de la page',
            ])
            ->setHelp('Rempli automatiquement depuis le titre, modifiable si besoin.')
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Meta title')
            ->setColumns(6)
            ->setHelp('Titre SEO facultatif. Si vide, le titre de la page est utilisé.')
            ->hideOnIndex();

        yield TextField::new('metaDescription', 'Meta description')
            ->setColumns(6)
            ->setHelp('Description SEO facultative. Si vide, un extrait du contenu est utilisé.')
            ->hideOnIndex();

        yield ImageField::new('heroImage', 'Image hero')
            ->setColumns(12)
            ->setBasePath('uploads/pages')
            ->setUploadDir('public/uploads/pages')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Image d'illustration de la page, affichée dans l'en-tête de la page publique.")
            ->hideOnIndex();

        yield TextEditorField::new('content', 'Contenu')
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
            ->setHelp('Utilise les boutons du haut pour créer des titres, des paragraphes, du gras et des listes.')
            ->hideOnIndex();

        yield FormField::addFieldset('Affichage')
            ->setHelp('Choisis où la page apparaît, dans quel ordre et avec quel statut.');

        yield ChoiceField::new('placement', 'Emplacement')
            ->setChoices([
                'Aucun' => PagePlacement::None,
                'Menu haut' => PagePlacement::Header,
                'Pied de page' => PagePlacement::Footer,
                'Haut + pied de page' => PagePlacement::Both,
            ])
            ->renderExpanded()
            ->setColumns(8)
            ->setHelp('Définit où la page doit apparaître dans la navigation.');

        yield IntegerField::new('menuOrder', 'Ordre')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
                'aria-label' => "Ordre d'affichage",
            ])
            ->setHelp('Plus le nombre est petit, plus la page remonte dans le menu.');

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publiée' => ContentStatus::Published,
                'Archivée' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp('Définit si la page est en brouillon, visible publiquement ou archivée.');
    }
}
