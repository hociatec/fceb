<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\HomeSection;
use App\Repository\HomeSectionRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
            ->setSearchFields(['title', 'subtitle', 'content', 'secondaryContent', 'sectionKey'])
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

        yield TextField::new('subtitle', 'Sous-titre affiché')
            ->setColumns(12)
            ->setRequired(false)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Sous-titre de la section',
                'placeholder' => 'Phrase courte sous le titre',
            ])
            ->setHelp("Petit texte d'accompagnement sous le titre.");

        yield TextareaField::new('content', 'Contenu principal')
            ->setColumns(12)
            ->setRequired(false)
            ->setNumOfRows(5)
            ->setHelp("Texte éditorial principal du bloc. Selon le bloc, il sert d'introduction, d'accroche ou d'explication.");

        yield TextareaField::new('secondaryContent', 'Contenu complémentaire')
            ->setColumns(12)
            ->setRequired(false)
            ->setNumOfRows(4)
            ->setHelp("Texte secondaire du bloc. Utilise-le pour une note, un libellé complémentaire ou une seconde accroche.");

        yield FormField::addFieldset('Actualités pilotées')
            ->renderCollapsed()
            ->setHelp("Ces champs sont utilisés uniquement par le bloc « Actualité à la une ». Ils permettent de choisir manuellement l’article principal et l’ordre des autres actualités.");

        yield AssociationField::new('featuredArticle', 'Article principal')
            ->renderAsNativeWidget()
            ->setColumns(12)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Utiliser la sélection automatique')
            ->setFormTypeOption('query_builder', static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
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
            ->setHelp("Si ce champ est vide, l'accueil reprend automatiquement l’article marqué « Actualité à la une » le plus récent.");

        yield AssociationField::new('secondaryArticleOne', 'Autre actualité n°1')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
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
                'aria-label' => 'Première autre actualité du bloc accueil',
            ]);

        yield AssociationField::new('secondaryArticleTwo', 'Autre actualité n°2')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
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
                'aria-label' => 'Deuxième autre actualité du bloc accueil',
            ]);

        yield AssociationField::new('secondaryArticleThree', 'Autre actualité n°3')
            ->renderAsNativeWidget()
            ->setColumns(4)
            ->setRequired(false)
            ->setFormTypeOption('placeholder', 'Sélection automatique')
            ->setFormTypeOption('query_builder', static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
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
                'aria-label' => 'Troisième autre actualité du bloc accueil',
            ]);

        yield ImageField::new('image', 'Image du bloc')
            ->setColumns(12)
            ->setBasePath('uploads/home-sections')
            ->setUploadDir('public/uploads/home-sections')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setRequired(false)
            ->setHelp("Image spécifique au bloc. Si elle est vide, l'accueil utilise le visuel dynamique disponible.")
            ->hideOnIndex();

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
