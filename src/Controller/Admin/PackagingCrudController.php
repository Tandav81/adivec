<?php

namespace App\Controller\Admin;

use App\Entity\Packaging;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PackagingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Packaging::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('contenant'),
            TextField::new('poids')
        ];
    }

}
