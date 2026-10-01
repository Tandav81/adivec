<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Maillage interne actualités ↔ fiches produits (BlogPost::$relatedProducts) + liens initiaux.
 * Les liens restent modifiables dans l'admin (champ « Produits associés » des actualités).
 */
final class Version20261001130000 extends AbstractMigration
{
    private const LINKS = [
        'etude-UE' => ['amidon-natif-de-pois', 'isolat-de-proteine-de-pois', 'fibre-de-pois', 'amidons-modifies-de-pois'],
        'sauce-soja' => ['lecithines-de-soja', 'lecithine-de-soja-bio', 'sauce-soja-standard', 'sauce-soja-bio'],
        'fromage-vegan' => ['isolat-de-proteine-de-pois', 'amidons-modifies-de-pomme-de-terre-clean-label', 'amidons-modifies-de-pois-clean-label'],
        'umami' => ['sauce-soja-standard', 'sauce-soja-umami-eleve', 'sauce-soja-umami-eleve-deshydratee', 'marinade-teriyaki'],
        'des-solutions-clean-label-pensees-pour-les-applications-reelles-texture-stabilite-et-simplicite-detiquetage' => [
            'amidons-modifies-de-pomme-de-terre-clean-label', 'amidons-modifies-de-pois-clean-label',
        ],
    ];

    public function getDescription(): string
    {
        return 'Table blog_post_product (produits associés aux actualités) et liens initiaux';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE blog_post_product (blog_post_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_B47E5C19A77FBEAF (blog_post_id), INDEX IDX_B47E5C194584665A (product_id), PRIMARY KEY(blog_post_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blog_post_product ADD CONSTRAINT FK_B47E5C19A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blog_post_product ADD CONSTRAINT FK_B47E5C194584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');

        foreach (self::LINKS as $newsSlug => $productSlugs) {
            foreach ($productSlugs as $productSlug) {
                $this->addSql('INSERT IGNORE INTO blog_post_product (blog_post_id, product_id)
                    SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = :p WHERE b.slug = :b',
                    ['b' => $newsSlug, 'p' => $productSlug]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE blog_post_product DROP FOREIGN KEY FK_B47E5C19A77FBEAF');
        $this->addSql('ALTER TABLE blog_post_product DROP FOREIGN KEY FK_B47E5C194584665A');
        $this->addSql('DROP TABLE blog_post_product');
    }
}
