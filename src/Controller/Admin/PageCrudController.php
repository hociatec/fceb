<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Enum\ContentStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
            ->setDefaultSort(['title' => 'ASC'])
            ->setPaginatorPageSize(10)
            ->setSearchFields(['title', 'slug', 'systemKey'])
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
            ->disable(Action::BATCH_DELETE, Action::DETAIL, Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_INDEX, $preview)
            ->add(Crud::PAGE_EDIT, $preview)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Contenu de la page')
            ->renderCollapsed()
            ->setHelp('Crée ou modifie une page d’information visible sur le site. La présence dans le header et le footer se gère désormais dans Navigation.');

        yield TextField::new('title', 'Titre')
            ->setColumns(8)
            ->setFormTypeOption('attr', [
                'data-slug-source' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Titre de la page',
                'placeholder' => 'Exemple : Découvrir le cécifoot',
            ])
            ->setHelp('Titre visible dans la page.');

        yield TextField::new('slug', 'Adresse web')
            ->setColumns(4)
            ->setFormTypeOption('attr', [
                'data-slug-target' => 'page',
                'autocomplete' => 'off',
                'aria-label' => 'Adresse web de la page',
            ])
            ->setHelp('Générée automatiquement depuis le titre, modifiable si besoin.')
            ->hideOnIndex();

        yield TextField::new('systemKey', 'Clé système')
            ->setColumns(4)
            ->setRequired(false)
            ->setHelp('Réservé aux pages structurelles du site. À ne pas modifier sans besoin technique.')
            ->hideOnIndex();

        yield ImageField::new('heroImage', "Image d'en-tête")
            ->setColumns(12)
            ->setBasePath('uploads/pages')
            ->setUploadDir('public/uploads/pages')
            ->setUploadedFileNamePattern('[contenthash].[extension]')
            ->setHelp('Image illustrant la page.')
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
                'aria-label' => 'Contenu de la page',
                'data-page-rich-editor' => '1',
            ])
            ->setHelp('Utilise les boutons du haut pour structurer la page.')
            ->hideOnIndex();

        yield ChoiceField::new('status', 'Visibilité')
            ->setChoices([
                'Brouillon' => ContentStatus::Draft,
                'Publiée' => ContentStatus::Published,
                'Archivée' => ContentStatus::Archived,
            ])
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp('Brouillon : non visible. Publiée : visible. Archivée : conservée sans être mise en avant.');

        yield FormField::addFieldset('Référencement')
            ->renderCollapsed()
            ->setHelp('Champs facultatifs pour Google et le partage social.')
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Titre SEO')
            ->setColumns(6)
            ->setHelp('Laisse vide pour reprendre automatiquement le titre de la page.')
            ->hideOnIndex();

        yield TextField::new('metaDescription', 'Description SEO')
            ->setColumns(6)
            ->setHelp('Laisse vide pour reprendre automatiquement un extrait du contenu.')
            ->hideOnIndex();
    }
}
