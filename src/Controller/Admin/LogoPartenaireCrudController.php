<?php

namespace App\Controller\Admin;

use App\Entity\LogoPartenaire;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogoPartenaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LogoPartenaire::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            ImageField::new('logo')
                ->setBasePath('uploads/images/logos')
                ->setUploadDir('public/uploads/images/logos')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }
}
