<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Enum\ArticlePlacement;
use App\Enum\ContentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setDefaultSort(['publishedAt' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['title', 'excerpt', 'slug'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $preview = Action::new('preview', 'Prévisualiser')
            ->linkToRoute('site_article', static fn (Article $article): array => [
                'slug' => $article->getSlug(),
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
        yield FormField::addFieldset("Informations de l'article")
            ->setHelp("Commence par le titre, le résumé et l'image, puis saisis le contenu complet.");

        yield TextField::new('title', 'Titre')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'article',
                'autocomplete' => 'off',
                'aria-label' => "Titre de l'article",
                'placeholder' => 'Exemple : Victoire à Lens pour La Bassée',
            ])
            ->setHelp("Titre affiché sur le site et dans la liste des actualités.");

        yield TextField::new('slug', 'Adresse web')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'article',
                'autocomplete' => 'off',
                'aria-label' => "Adresse web de l'article",
            ])
            ->setHelp('Générée automatiquement depuis le titre, modifiable si besoin.')
            ->hideOnIndex();

        yield TextareaField::new('excerpt', 'Résumé court')
            ->setColumns(12)
            ->setFormTypeOption('attr', [
                'aria-label' => "Résumé court de l'article",
                'rows' => 4,
                'placeholder' => "Quelques lignes pour résumer l'article sur l'accueil et dans les listes.",
            ])
            ->setHelp("Court texte d'accroche affiché avant d'ouvrir l'article.");

        yield ImageField::new('coverImage', 'Image principale')
            ->setColumns(12)
            ->setBasePath('uploads/articles')
            ->setUploadDir('public/uploads/articles')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Image d'illustration de l'article.")
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
                'aria-label' => "Contenu complet de l'article",
                'data-page-rich-editor' => '1',
            ])
            ->setHelp('Utilise les boutons du haut pour créer des titres, listes et paragraphes.')
            ->hideOnIndex();

        yield FormField::addFieldset('Publication')
            ->setHelp("Choisis où l'article apparaît, pour quelle saison, et s'il est visible sur le site.");

        yield DateTimeField::new('publishedAt', 'Date de publication')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date et heure de publication',
            ])
            ->setHelp('Date affichée publiquement sur le site.');

        yield ChoiceField::new('placement', "Emplacement principal")
            ->setChoices([
                'Aucun emplacement spécial' => ArticlePlacement::None,
                'Accueil' => ArticlePlacement::Homepage,
                'Saison en cours' => ArticlePlacement::CurrentSeason,
                'Archive' => ArticlePlacement::Archive,
            ])
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp("Choisis si l'article doit être mis en avant à un endroit précis.");

        yield AssociationField::new('season', 'Saison')
            ->setColumns(6)
            ->setHelp("Saison liée à l'article, si besoin.");

        yield AssociationField::new('author', 'Auteur')
            ->setColumns(6)
            ->setHelp("Personne à afficher comme auteur de l'article.");

        yield ChoiceField::new('status', 'Visibilité')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publié' => ContentStatus::Published,
                'Archivé' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp("Brouillon : non visible. Publié : visible. Archivé : conservé sans être mis en avant.");

        yield FormField::addFieldset('Référencement')
            ->setHelp('Champs facultatifs pour Google et le partage social.')
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Titre SEO')
            ->setColumns(6)
            ->setHelp("Laisse vide pour reprendre automatiquement le titre de l'article.")
            ->hideOnIndex();

        yield TextareaField::new('metaDescription', 'Description SEO')
            ->setColumns(6)
            ->setNumOfRows(3)
            ->setHelp('Laisse vide pour reprendre automatiquement le résumé court.')
            ->hideOnIndex();
    }
}
