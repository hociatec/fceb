<?php

namespace App\Controller\Admin;

use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SeasonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Season::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Saison')
            ->setEntityLabelInPlural('Saisons')
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['name', 'slug'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom de la saison')
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'season',
                'autocomplete' => 'off',
                'aria-label' => 'Nom de la saison',
                'placeholder' => 'Exemple : Saison 2026-2027',
            ])
            ->setHelp('Nom visible dans le site et dans les listes admin.');

        yield TextField::new('slug', 'Adresse web')
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'season',
                'autocomplete' => 'off',
                'aria-label' => 'Adresse web de la saison',
            ])
            ->setHelp('Générée automatiquement depuis le nom, modifiable si besoin.')
            ->hideOnIndex();

        yield DateField::new('startDate', 'Début de saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date de début de la saison',
            ])
            ->setHelp('Date de début officielle.');

        yield DateField::new('endDate', 'Fin de saison')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Date de fin de la saison',
            ])
            ->setHelp('Date de fin officielle.');

        yield BooleanField::new('isCurrent', 'Saison en cours')
            ->renderAsSwitch(false)
            ->setFormTypeOption('attr', [
                'aria-label' => 'Définir cette saison comme saison en cours',
            ])
            ->setHelp('Si cette saison est cochée, toutes les autres saisons en cours seront décochées automatiquement.');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Season) {
            $this->unsetOtherCurrentSeasons($entityManager, $entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Season) {
            $this->unsetOtherCurrentSeasons($entityManager, $entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function unsetOtherCurrentSeasons(EntityManagerInterface $entityManager, Season $season): void
    {
        if (!$season->isCurrent()) {
            return;
        }

        $queryBuilder = $entityManager->createQueryBuilder()
            ->update(Season::class, 's')
            ->set('s.isCurrent', ':notCurrent')
            ->where('s.isCurrent = :current')
            ->setParameter('notCurrent', false)
            ->setParameter('current', true);

        if (null !== $season->getId()) {
            $queryBuilder
                ->andWhere('s.id != :seasonId')
                ->setParameter('seasonId', $season->getId());
        }

        $queryBuilder->getQuery()->execute();
    }
}
