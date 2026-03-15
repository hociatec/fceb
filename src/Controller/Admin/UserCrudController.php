<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(10)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::BATCH_DELETE, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action) => $action->setLabel('Supprimer'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        yield FormField::addFieldset('Identité')
            ->setHelp("Renseigne les informations de connexion et d'affichage du compte.");

        yield TextField::new('fullName', 'Nom complet')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'autocomplete' => 'name',
                'aria-label' => "Nom complet de l'utilisateur",
                'placeholder' => 'Exemple : Marie Dupont',
            ])
            ->setHelp("Nom affiché dans l'administration.");

        yield EmailField::new('email', 'Adresse e-mail')
            ->setColumns(6)
            ->setFormTypeOption('attr', [
                'autocomplete' => 'email',
                'aria-label' => "Adresse e-mail de l'utilisateur",
                'placeholder' => 'nom@exemple.fr',
            ])
            ->setHelp('Adresse utilisée pour la connexion.');

        yield FormField::addFieldset('Droits et sécurité')
            ->setHelp("Définis les droits d'accès et le mot de passe du compte.");

        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Éditeur' => 'ROLE_EDITOR',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setColumns(6)
            ->setHelp("Choisit les droits d'accès de l'utilisateur.");

        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setColumns(6)
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setFormTypeOption('attr', [
                'autocomplete' => 'new-password',
                'aria-label' => "Mot de passe de l'utilisateur",
                'placeholder' => 'Nouveau mot de passe',
            ])
            ->setHelp('Laisser vide pour conserver le mot de passe actuel lors de la modification.');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()));
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()));
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
