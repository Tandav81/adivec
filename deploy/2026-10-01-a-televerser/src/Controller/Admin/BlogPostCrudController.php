<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BlogPostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BlogPost::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            SlugField::new('slug')
                ->setTargetFieldName('title')
                ->onlyOnForms()
                ->setHelp('Généré automatiquement à partir du titre.'),
            TextEditorField::new('description')
                ->setNumOfRows(20)
                ->setTrixEditorConfig([
                    // Le bouton "Titre" génère un <h2> (et non un <h1>) : le H1 de la page
                    // reste le titre de la news, ce qui évite les H1 multiples pour le SEO.
                    'blockAttributes' => [
                        'heading1' => ['tagName' => 'h2'],
                    ],
                    // Attribut "Mettre en avant" → <mark>, stylé en vert Adivec sur le site.
                    'textAttributes' => [
                        'highlight' => ['tagName' => 'mark', 'inheritable' => true],
                    ],
                ])
                ->setHelp('Utilisez « Titre de section » pour structurer l\'article, « Gras » ou « Mettre en avant » pour les mots-clés.'),
            BooleanField::new('visible'),
            AssociationField::new('relatedProducts', 'Produits associés')
                ->onlyOnForms()
                ->autocomplete()
                ->setHelp('Fiches produits citées dans l\'article : liens croisés article ↔ fiches (référencement).'),
            DateTimeField::new('createdAt', 'Date de publication')
                ->setHelp('Laisser vide pour utiliser la date de création. Affichée sur l\'article et transmise à Google.'),
            ImageField::new('image')
                ->setBasePath('uploads/images/blog')
                ->setUploadDir('public/uploads/images/blog')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
        ];
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('css/admin/trix-adivec.css')
            ->addJsFile('js/admin/trix-adivec.js');
    }
}
