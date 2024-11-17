<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            AssociationField::new('type'),
            AssociationField::new('applications'),
            TextField::new('packaging'),
            ImageField::new('image')
        ->setBasePath('uploads/images/products')
        ->setUploadDir('public/uploads/images/products')
        ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }
}
