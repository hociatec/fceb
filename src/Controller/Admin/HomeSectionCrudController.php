<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\HomeSection;
use App\Repository\ArticleRepository;
use App\Repository\HomeSectionRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HomeSectionCrudController extends AbstractCrudController
{
    public function __construct(private readonly HomeSectionRepository $homeSectionRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return HomeSection::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular("Section d'accueil")
            ->setEntityLabelInPlural("Blocs d'accueil")
            ->setDefaultSort(['displayOrder' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields([
                'title',
                'content',
                'sectionKey',
            ])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::DELETE, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer le bloc'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer'));

        if ([] === $this->homeSectionRepository->missingSectionChoices()) {
            $actions = $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $sectionChoices = Crud::PAGE_NEW === $pageName
            ? $this->homeSectionRepository->missingSectionChoices()
            : HomeSection::availableSectionChoices();

        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('sectionKey', 'Bloc')
                ->setChoices(HomeSection::availableSectionChoices());

            return;
        }

        yield FormField::addFieldset("Configuration de l'accueil")
            ->renderCollapsed()
            ->setHelp("Chaque ligne correspond à un vrai bloc visible sur la page d'accueil.");

        yield ChoiceField::new('sectionKey', 'Bloc concerné')
            ->setChoices($sectionChoices)
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setFormTypeOption('placeholder', 'Choisir un bloc')
            ->setFormTypeOption('attr', [
                'aria-label' => "Bloc d'accueil concerné",
            ])
            ->setFormTypeOption('disabled', Crud::PAGE_EDIT === $pageName)
            ->setHelp(Crud::PAGE_NEW === $pageName
                ? "Seuls les blocs manquants peuvent être recréés depuis l'administration."
                : "Le type de bloc est fixe pour garder l'accueil cohérent.");

        yield TextField::new('title', 'Titre affiché')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Titre de la section',
                'placeholder' => "Titre affiché sur l'accueil",
            ])
            ->setHelp('Titre visible au-dessus du bloc.');

        yield ChoiceField::new('titleTag', 'Niveau du titre')
            ->setChoices(HomeSection::availableTitleTagChoices())
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setHelp('Permet de choisir la balise HTML du titre du bloc.');

        yield TextEditorField::new('content', 'Contenu du bloc')
            ->setColumns(12)
            ->setRequired(false)
            ->setNumOfRows(10)
            ->setTrixEditorConfig([
                'blockAttributes' => [
                    'default' => ['tagName' => 'p'],
                    'heading1' => ['tagName' => 'h2'],
                    'heading2' => ['tagName' => 'h3'],
                ],
            ])
            ->setFormTypeOption('attr', [
                'aria-label' => 'Contenu du bloc',
                'data-page-rich-editor' => '1',
            ])
            ->setHelp('Rédige librement tout le contenu du bloc ici. Exemples : [cta label="Mon bouton" url="/mon-lien"], [cta label="Site externe" url="https://..." style="secondary" target="blank"], [separator], [quote]Citation[/quote].');

        yield ImageField::new('image', 'Image du bloc')
            ->setColumns(12)
            ->setBasePath('uploads/home-sections')
            ->setUploadDir('public/uploads/home-sections')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setRequired(false)
            ->setHelp("Image spécifique au bloc. Si elle est vide, l'accueil utilise le visuel dynamique disponible.")
            ->hideOnIndex();

        yield FormField::addFieldset('Mise en forme')
            ->renderCollapsed()
            ->setHelp('Réglages visuels du bloc sur la page d’accueil.');

        yield ChoiceField::new('textAlignment', 'Alignement du texte')
            ->setChoices(HomeSection::availableTextAlignmentChoices())
            ->renderAsNativeWidget()
            ->setColumns(4);
        yield ChoiceField::new('layoutWidth', 'Largeur du bloc')
            ->setChoices(HomeSection::availableLayoutWidthChoices())
            ->renderAsNativeWidget()
            ->setColumns(4);
        yield ChoiceField::new('appearance', 'Variante visuelle')
            ->setChoices(HomeSection::availableAppearanceChoices())
            ->renderAsNativeWidget()
            ->setColumns(4);
        yield ChoiceField::new('accentTone', 'Couleur d’accent')
            ->setChoices(HomeSection::availableAccentToneChoices())
            ->renderAsNativeWidget()
            ->setColumns(4);

        yield BooleanField::new('showImage', 'Afficher l’image')
            ->renderAsSwitch(false)
            ->setColumns(6);
        yield ChoiceField::new('imagePosition', "Position de l'image")
            ->setChoices(HomeSection::availableImagePositionChoices())
            ->renderAsNativeWidget()
            ->setColumns(6);

        yield FormField::addFieldset('Éléments internes')
            ->renderCollapsed()
            ->setHelp("Affiche ou masque certains éléments du bloc sans toucher au contenu source.");

        yield BooleanField::new('showTag', 'Afficher le tag')
            ->renderAsSwitch(false)
            ->setColumns(3);
        yield BooleanField::new('showMeta', 'Afficher les métadonnées')
            ->renderAsSwitch(false)
            ->setColumns(3);
        yield BooleanField::new('showExcerpt', 'Afficher les extraits')
            ->renderAsSwitch(false)
            ->setColumns(3);
        yield BooleanField::new('showScore', 'Afficher le score')
            ->renderAsSwitch(false)
            ->setColumns(3);

        yield IntegerField::new('upcomingMatchesLimit', 'Nombre de matchs à afficher')
            ->setColumns(4)
            ->setHelp("Utilisé surtout par le bloc « Prochain match ». 1 = seulement le prochain match, 4 = le prochain + 3 suivants.")
            ->setFormTypeOption('attr', [
                'min' => 1,
                'inputmode' => 'numeric',
            ]);

        yield FormField::addFieldset('Actualités pilotées')
            ->renderCollapsed()
            ->setHelp("Ces champs sont utilisés uniquement par le bloc « Actualité à la une ». Ils permettent de choisir manuellement l'article principal et l'ordre des autres actualités.");

        yield AssociationField::new('featuredArticle', 'Article principal')
            ->renderAsNativeWidget()
            ->setColumns(12)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Utiliser la sélection automatique')
            ->setFormTypeOption('query_builder', static function (ArticleRepository $repository) {
                return $repository->createQueryBuilder('entity')
                    ->orderBy('entity.publishedAt', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?Article $article): string {
                if (!$article instanceof Article) {
                    return '';
                }

                return sprintf(
                    '%s · %s',
                    (string) $article->getTitle(),
                    $article->getPublishedAt()?->format('d/m/Y') ?? 'sans date'
                );
            })
            ->setFormTypeOption('attr', [
                'aria-label' => "Article principal du bloc d'accueil",
            ])
            ->setHelp("Si ce champ est vide, l'accueil reprend automatiquement l'article marqué « Actualité à la une » le plus récent.");

        yield AssociationField::new('secondaryArticleOne', 'Autre actualité n°1')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (ArticleRepository $repository) {
                return $repository->createQueryBuilder('entity')
                    ->orderBy('entity.publishedAt', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?Article $article): string {
                if (!$article instanceof Article) {
                    return '';
                }

                return sprintf(
                    '%s · %s',
                    (string) $article->getTitle(),
                    $article->getPublishedAt()?->format('d/m/Y') ?? 'sans date'
                );
            });

        yield AssociationField::new('secondaryArticleTwo', 'Autre actualité n°2')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (ArticleRepository $repository) {
                return $repository->createQueryBuilder('entity')
                    ->orderBy('entity.publishedAt', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?Article $article): string {
                if (!$article instanceof Article) {
                    return '';
                }

                return sprintf(
                    '%s · %s',
                    (string) $article->getTitle(),
                    $article->getPublishedAt()?->format('d/m/Y') ?? 'sans date'
                );
            });

        yield AssociationField::new('secondaryArticleThree', 'Autre actualité n°3')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (ArticleRepository $repository) {
                return $repository->createQueryBuilder('entity')
                    ->orderBy('entity.publishedAt', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?Article $article): string {
                if (!$article instanceof Article) {
                    return '';
                }

                return sprintf(
                    '%s · %s',
                    (string) $article->getTitle(),
                    $article->getPublishedAt()?->format('d/m/Y') ?? 'sans date'
                );
            });

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
