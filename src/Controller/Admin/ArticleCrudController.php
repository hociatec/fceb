<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\MatchGame;
use App\Entity\User;
use App\Enum\ArticleHomepageSlot;
use App\Enum\ContentStatus;
use App\Repository\MatchGameRepository;
use App\Repository\SeasonRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
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
    public function __construct(private readonly SeasonRepository $seasonRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function createEntity(string $entityFqcn): Article
    {
        $article = new Article();
        $article->setPublishedAt(new \DateTimeImmutable());

        $currentSeason = $this->seasonRepository->findCurrentSeason();
        if (null !== $currentSeason) {
            $article->setSeason($currentSeason);
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $article->setAuthor($user);
        }

        return $article;
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('season', 'Saison'))
            ->add(EntityFilter::new('author', 'Auteur'))
            ->add(ChoiceFilter::new('status', 'Visibilité')->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publié' => ContentStatus::Published,
                'Archivé' => ContentStatus::Archived,
            ]))
            ->add(ChoiceFilter::new('homepageSlot', "Affichage sur l'accueil")->setChoices([
                "Ne pas afficher sur l'accueil" => ArticleHomepageSlot::None,
                'Actualité à la une' => ArticleHomepageSlot::Featured,
                'Autres actualités' => ArticleHomepageSlot::Secondary,
            ]))
            ->add(DateTimeFilter::new('publishedAt', 'Date de publication'));
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
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_EDIT, $preview)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset("Informations de l'article")
            ->renderCollapsed()
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
            ->renderCollapsed()
            ->setHelp("Choisis où l'article apparaît, pour quelle saison, et s'il est visible sur le site.");

        yield DateTimeField::new('publishedAt', 'Date de publication')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date et heure de publication',
            ])
            ->setHelp('Date affichée publiquement sur le site.');

        yield ChoiceField::new('homepageSlot', "Affichage sur l'accueil")
            ->setChoices([
                "Ne pas afficher sur l'accueil" => ArticleHomepageSlot::None,
                'Actualité à la une' => ArticleHomepageSlot::Featured,
                'Autres actualités' => ArticleHomepageSlot::Secondary,
            ])
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp("Si plusieurs articles partagent le même emplacement, les plus récents passent en priorité.");

        yield AssociationField::new('season', 'Saison')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setFormTypeOption('placeholder', 'Choisir une saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Saison de l’article',
            ])
            ->setHelp("Saison liée à l'article, si besoin.");

        yield AssociationField::new('author', 'Auteur')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setFormTypeOption('placeholder', 'Choisir un auteur')
            ->setFormTypeOption('attr', [
                'aria-label' => "Auteur de l'article",
            ])
            ->setHelp("Personne à afficher comme auteur de l'article.");

        yield AssociationField::new('linkedMatch', 'Match lié')
            ->renderAsNativeWidget()
            ->setColumns(12)
            ->setFormTypeOption('placeholder', 'Aucun match lié')
            ->setFormTypeOption('query_builder', static function (MatchGameRepository $repository) {
                return $repository->createQueryBuilder('entity')
                    ->orderBy('entity.matchDate', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?MatchGame $match): string {
                if (!$match instanceof MatchGame) {
                    return '';
                }

                $label = sprintf(
                    '%s · %s',
                    $match->getPoster(),
                    $match->getMatchDate()?->format('d/m/Y H:i') ?? 'date inconnue'
                );

                if ($match->getCompetition()) {
                    $label .= sprintf(' · %s', $match->getCompetition());
                }

                return $label;
            })
            ->setFormTypeOption('attr', [
                'aria-label' => 'Match lié à cet article',
            ])
            ->setHelp('Choisis ici le match auquel cet article sert de compte-rendu. Cette liaison met aussi à jour la fiche match.');

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
            ->renderCollapsed()
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
