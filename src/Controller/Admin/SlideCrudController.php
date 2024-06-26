<?php

namespace App\Controller\Admin;

use App\Entity\Slide;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SlideCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Slide::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextEditorField::new('subtitle'),
            DateField::new('dateDebut'),
            DateField::new('dateFin'),
            ImageField::new('image')
                ->setBasePath('uploads/images/slides')
                ->setUploadDir('public/uploads/images/slides')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }
}
