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
            ->setHelp("Renseigne le titre, le slug et la description courte avant le contenu complet.");

        yield TextField::new('title', 'Titre')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'article',
                'autocomplete' => 'off',
                'aria-label' => "Titre de l'article",
                'placeholder' => 'Exemple : Une victoire importante à Lens',
            ])
            ->setHelp("Titre affiché sur le site et dans les listes d'actualités.");

        yield TextField::new('slug', 'Slug')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'article',
                'autocomplete' => 'off',
                'aria-label' => "Slug de l'article",
            ])
            ->setHelp('Rempli automatiquement depuis le titre, modifiable si besoin.')
            ->hideOnIndex();

        yield TextareaField::new('excerpt', 'Description courte')
            ->setColumns(12)
            ->setFormTypeOption('attr', [
                'aria-label' => "Description courte de l'article",
                'rows' => 4,
                'placeholder' => "Résumé court affiché sur l'accueil ou dans les listes d'articles.",
            ])
            ->setHelp('Court texte affiché dans les cartes et aperçus.');

        yield TextField::new('metaTitle', 'Meta title')
            ->setColumns(6)
            ->setHelp('Titre SEO facultatif. Si vide, le titre de l’article est utilisé.')
            ->hideOnIndex();

        yield TextareaField::new('metaDescription', 'Meta description')
            ->setColumns(6)
            ->setNumOfRows(3)
            ->setHelp('Description SEO facultative. Si vide, la description courte est utilisée.')
            ->hideOnIndex();

        yield ImageField::new('coverImage', 'Image de couverture')
            ->setColumns(12)
            ->setBasePath('uploads/articles')
            ->setUploadDir('public/uploads/articles')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Image principale de l'article, affichée sur la page article et utilisable ensuite dans les listes.")
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
                'aria-label' => "Contenu complet de l'article",
                'data-page-rich-editor' => '1',
            ])
            ->setHelp('Utilise les boutons du haut pour créer des titres, des paragraphes, des listes et de la mise en forme.')
            ->hideOnIndex();

        yield FormField::addFieldset('Publication')
            ->setHelp("Définit quand, où et dans quel état l'article doit apparaître.");

        yield DateTimeField::new('publishedAt', 'Date de publication')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date et heure de publication',
            ])
            ->setHelp('Date de publication affichée sur le site.');

        yield ChoiceField::new('placement', "Section d'affichage")
            ->setChoices([
                'Aucune' => ArticlePlacement::None,
                'Accueil' => ArticlePlacement::Homepage,
                'Saison en cours' => ArticlePlacement::CurrentSeason,
                'Archive' => ArticlePlacement::Archive,
            ])
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp("Choisit où l'article doit apparaître sur le site.");

        yield AssociationField::new('season', 'Saison')
            ->setColumns(6)
            ->setHelp("Saison à laquelle rattacher l'article.");

        yield AssociationField::new('author', 'Auteur')
            ->setColumns(6)
            ->setHelp("Compte auteur associé à l'article.");

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publié' => ContentStatus::Published,
                'Archivé' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp("Définit si l'article est en brouillon, visible publiquement ou archivé.");
    }
}
