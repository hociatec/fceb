<?php

namespace App\Controller\Admin;

use App\Entity\MatchGame;
use App\Enum\MatchStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MatchGameCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MatchGame::class;
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Créer le match'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static fn (Action $action) => $action->setLabel('Enregistrer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations du match')
            ->setHelp("Renseigne d'abord l'adversaire, la date, le lieu et le statut du match.");

        yield AssociationField::new('season', 'Saison')
            ->renderAsNativeWidget()
            ->setColumns(6)
            ->setHelp('Saison à laquelle rattacher ce match.');

        yield ChoiceField::new('competition', 'Compétition')
            ->setChoices([
                'Championnat' => 'Championnat',
                'Coupe de France' => 'Coupe de France',
            ])
            ->renderExpanded()
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Compétition',
            ])
            ->setHelp('Choisis le type de rencontre.');

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

        yield ChoiceField::new('side', 'Lieu de jeu')
            ->setChoices([
                'À domicile' => 'home',
                'À l’extérieur' => 'away',
            ])
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp('Indique si La Bassée reçoit ou se déplace.');

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
    }
}
