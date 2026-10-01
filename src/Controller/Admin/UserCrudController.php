<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email'),
            // Le mot de passe n'est jamais édité ici : il serait enregistré en clair (non hashé).
            ChoiceField::new('roles')
                ->setChoices(['Admin' => 'ROLE_ADMIN', 'Super admin' => 'ROLE_SUPER_ADMIN'])
                ->allowMultipleChoices()
                ->renderExpanded(),
            BooleanField::new('isVerified')
        ];
    }

}
