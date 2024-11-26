<?php

namespace App\Controller\Admin;

use App\Entity\Family;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FamilyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Family::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            ImageField::new('image')
                ->setBasePath('uploads/images/family')
                ->setUploadDir('public/uploads/images/family')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }

}
