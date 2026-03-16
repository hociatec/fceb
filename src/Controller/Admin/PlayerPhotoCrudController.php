<?php

namespace App\Controller\Admin;

use App\Entity\PlayerPhoto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PlayerPhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayerPhoto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Photo joueur')
            ->setEntityLabelInPlural('Photos joueurs')
            ->setDefaultSort(['displayOrder' => 'ASC', 'id' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['player.name', 'caption', 'photo'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $preview = Action::new('preview', 'Voir le joueur')
            ->linkToRoute('site_player_show', static fn (PlayerPhoto $playerPhoto): array => [
                'slug' => $playerPhoto->getPlayer()?->getSlug(),
                'preview' => 1,
            ])
            ->displayIf(static fn (PlayerPhoto $playerPhoto): bool => null !== $playerPhoto->getPlayer()?->getSlug())
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
        yield FormField::addFieldset('Photos complémentaires du joueur')
            ->renderCollapsed()
            ->setHelp('Ajoute des visuels supplémentaires pour enrichir la présentation du joueur dans l\'effectif.');

        yield AssociationField::new('player', 'Joueur')
            ->setColumns(8)
            ->renderAsNativeWidget()
            ->setFormTypeOption('placeholder', 'Choisir un joueur')
            ->setFormTypeOption('attr', [
                'aria-label' => 'Joueur concerné par la photo',
            ])
            ->setHelp('Joueur concerné par cette photo.');

        yield IntegerField::new('displayOrder', "Ordre d'affichage")
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'min' => 0,
                'inputmode' => 'numeric',
                'aria-label' => "Ordre d'affichage de la photo",
            ])
            ->setHelp('Plus le chiffre est petit, plus la photo remonte dans la galerie.');

        yield ImageField::new('photo', 'Photo')
            ->setColumns(12)
            ->setBasePath('uploads/players')
            ->setUploadDir('public/uploads/players')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp("Photo affichée dans la galerie du joueur. Si la photo principale est vide, la première photo complémentaire devient l'image visible sur le site.");

        yield TextField::new('caption', 'Légende')
            ->setColumns(12)
            ->setFormTypeOption('required', false)
            ->setHelp('Texte facultatif affiché sous la photo.');
    }
}
