<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\MatchGame;
use App\Enum\MatchStatus;
use App\Repository\SeasonRepository;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MatchGameCrudController extends AbstractCrudController
{
    public function __construct(private readonly SeasonRepository $seasonRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return MatchGame::class;
    }

    public function createEntity(string $entityFqcn): MatchGame
    {
        $match = new MatchGame();
        $match->setCompetition(MatchGame::COMPETITION_CHAMPIONNAT);

        $currentSeason = $this->seasonRepository->findCurrentSeason();
        if (null !== $currentSeason) {
            $match->setSeason($currentSeason);
        }

        return $match;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Match')
            ->setEntityLabelInPlural('Matchs')
            ->setDefaultSort(['matchDate' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['opponent', 'location', 'competition'])
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('season', 'Saison'))
            ->add(EntityFilter::new('linkedArticle', 'Article lié'))
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices([
                'Programmé' => MatchStatus::Scheduled,
                'Terminé' => MatchStatus::Completed,
                'Reporté' => MatchStatus::Postponed,
                'Annulé' => MatchStatus::Cancelled,
            ]))
            ->add(ChoiceFilter::new('competition', 'Compétition')->setChoices([
                MatchGame::COMPETITION_CHAMPIONNAT => MatchGame::COMPETITION_CHAMPIONNAT,
                MatchGame::COMPETITION_COUPE_DE_FRANCE => MatchGame::COMPETITION_COUPE_DE_FRANCE,
            ]))
            ->add(DateTimeFilter::new('matchDate', 'Date et heure'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer le match'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations du match')
            ->renderCollapsed()
            ->setHelp("Renseigne d'abord l'adversaire, la date, le lieu et le statut du match.");

        yield AssociationField::new('season', 'Saison')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setFormTypeOption('placeholder', 'Choisir une saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Saison du match',
            ])
            ->setHelp('Saison à laquelle rattacher ce match.');

        yield ChoiceField::new('competition', 'Compétition')
            ->setChoices(MatchGame::competitionChoices())
            ->renderAsNativeWidget()
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Compétition',
            ])
            ->setHelp('Choisis la compétition dans la liste déroulante.');

        yield TextField::new('opponent', 'Adversaire')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => "Nom de l'adversaire",
                'autocomplete' => 'off',
                'placeholder' => 'Exemple : RC Lens Cécifoot',
            ])
            ->setHelp('Nom du club adverse.');

        yield TextField::new('location', 'Lieu')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Lieu du match',
                'autocomplete' => 'off',
                'placeholder' => 'Exemple : Stade Roland Joly, La Bassée',
            ])
            ->setHelp('Ville, stade ou salle.');

        yield DateTimeField::new('matchDate', 'Date et heure')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date et heure du match',
            ])
            ->setHelp('Date prévue pour la rencontre.');

        yield ChoiceField::new('side', 'Ordre des équipes')
            ->setChoices([
                'Club affiché en premier' => 'home',
                'Club affiché en second' => 'away',
            ])
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp("Ce choix suit l'ordre des équipes dans Tournify et sert à l'affichage du score. Le lieu du match se renseigne séparément.");

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Programmé' => MatchStatus::Scheduled,
                'Terminé' => MatchStatus::Completed,
                'Reporté' => MatchStatus::Postponed,
                'Annulé' => MatchStatus::Cancelled,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp('État actuel du match.');

        yield FormField::addFieldset('Résultat')
            ->renderCollapsed()
            ->setHelp("Laisse ces champs vides tant que le match n'est pas terminé.");

        yield IntegerField::new('ourScore', 'Score du club')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Score du club',
                'min' => 0,
                'inputmode' => 'numeric',
                'placeholder' => '0',
            ])
            ->setHelp('À renseigner uniquement quand le match est terminé.');

        yield IntegerField::new('opponentScore', "Score de l'adversaire")
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => "Score de l'adversaire",
                'min' => 0,
                'inputmode' => 'numeric',
                'placeholder' => '0',
            ])
            ->setHelp('À renseigner uniquement quand le match est terminé.');

        yield FormField::addFieldset('Compte-rendu')
            ->renderCollapsed()
            ->setHelp("Lie ici l'article exact du match. Cette liaison est prioritaire sur la détection automatique par date et adversaire.");

        yield AssociationField::new('linkedArticle', 'Article lié')
            ->renderAsNativeWidget()
            ->setColumns(12)
            ->setFormTypeOption('query_builder', static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
                    ->orderBy('entity.publishedAt', 'DESC')
                    ->addOrderBy('entity.id', 'DESC');
            })
            ->setFormTypeOption('choice_label', static function (?Article $article): string {
                if (!$article instanceof Article) {
                    return '';
                }

                $publishedAt = $article->getPublishedAt();

                return null !== $publishedAt
                    ? sprintf('%s · %s', (string) $article->getTitle(), $publishedAt->format('d/m/Y'))
                    : (string) $article->getTitle();
            })
            ->setFormTypeOption('placeholder', 'Aucun article lié')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Article lié au match',
            ])
            ->setHelp("Utilisé sur le calendrier, la saison et le bloc d'accueil 'Dernier résultat'.");
    }
}
