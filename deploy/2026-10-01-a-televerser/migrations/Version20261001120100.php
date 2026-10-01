<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fiches produits en double (audit SEO du 30/09/2026).
 *
 * 1. Fusion des 7 vrais doublons « -1 » (même produit en alimentation humaine et en technique) :
 *    la fiche principale récupère la catégorie, les applications et les conditionnements du
 *    doublon, puis le doublon est supprimé. Redirection 301 : ProductController::SLUG_REDIRECTS.
 * 2. Les 3 « sauces soja -1 » sont des produits distincts (version déshydratée) : renommées.
 * 3. Slugs aux accents mal translittérés corrigés (« prot-eine » → « proteine »).
 */
final class Version20261001120100 extends AbstractMigration
{
    private const MERGES = [
        'amidon-natif-de-ble' => 'amidon-natif-de-ble-1',
        'gomme-de-guar' => 'gomme-de-guar-1',
        'gomme-de-xanthane' => 'gomme-de-xanthane-1',
        'fibre-de-pomme-de-terre' => 'fibre-de-pomme-de-terre-1',
        'cire-d-abeille' => 'cire-d-abeille-1',
        'cire-de-candelilla' => 'cire-de-candelilla-1',
        'cire-de-carnauba' => 'cire-de-carnauba-1',
    ];

    /** ancien slug => [nouveau nom, nouveau slug] */
    private const SOY_SAUCES = [
        'sauce-soja-standard-1' => ['Sauce soja standard déshydratée', 'sauce-soja-standard-deshydratee'],
        'sauce-soja-sans-gluten-tamari-1' => ['Sauce soja sans gluten (Tamari) déshydratée', 'sauce-soja-sans-gluten-tamari-deshydratee'],
        'sauce-soja-umami-elev-e-1' => ['Sauce soja Umami élevé déshydratée', 'sauce-soja-umami-eleve-deshydratee'],
    ];

    private const SLUG_FIXES = [
        'sauce-soja-umami-elev-e' => 'sauce-soja-umami-eleve',
        'carot-ene' => 'carotene',
        'concentrat-de-prot-eine-de-pois' => 'concentrat-de-proteine-de-pois',
        'exhausteur-de-go-ut-sauce-soja-sans-soja' => 'exhausteur-de-gout-sauce-soja-sans-soja',
        'isolat-de-prot-eine-de-pois' => 'isolat-de-proteine-de-pois',
        'l-ecithine-de-soja-bio' => 'lecithine-de-soja-bio',
        'l-ecithine-de-tournesol-bio' => 'lecithine-de-tournesol-bio',
        'l-ecithines-de-colza' => 'lecithines-de-colza',
        'l-ecithines-de-soja' => 'lecithines-de-soja',
        'l-ecithines-de-tournesol' => 'lecithines-de-tournesol',
        'lut-eine' => 'luteine',
        'prot-eine-de-bl-e' => 'proteine-de-ble',
        'r-eglisse' => 'reglisse',
        'sauce-soja-a-teneur-r-eduite-en-sel' => 'sauce-soja-a-teneur-reduite-en-sel',
        'sauce-soja-sucr-ee' => 'sauce-soja-sucree',
        'sauce-soja-taux-de-sel-r-eduit' => 'sauce-soja-taux-de-sel-reduit',
    ];

    public function getDescription(): string
    {
        return 'Fusion des fiches produits en double, sauces soja déshydratées renommées, slugs corrigés';
    }

    public function up(Schema $schema): void
    {
        foreach (self::MERGES as $main => $dup) {
            $p = ['main' => $main, 'dup' => $dup];
            // Catégorie du doublon → catégorie secondaire de la fiche principale
            $this->addSql('INSERT IGNORE INTO product_secondary_type (product_id, type_id)
                SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = :dup
                WHERE m.slug = :main AND d.type_id <> m.type_id', $p);
            // Applications et conditionnements : union des deux fiches
            $this->addSql('INSERT IGNORE INTO products_application (product_id, application_id)
                SELECT m.id, pa.application_id FROM products_application pa
                JOIN product d ON d.id = pa.product_id AND d.slug = :dup
                JOIN product m ON m.slug = :main', $p);
            $this->addSql('INSERT IGNORE INTO product_packaging (product_id, packaging_id)
                SELECT m.id, pp.packaging_id FROM product_packaging pp
                JOIN product d ON d.id = pp.product_id AND d.slug = :dup
                JOIN product m ON m.slug = :main', $p);
            // Fournisseur / description manquants sur la fiche principale : repris du doublon
            $this->addSql('UPDATE product m JOIN product d ON d.slug = :dup
                SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id),
                    m.description = COALESCE(m.description, d.description),
                    m.updated_at = NOW()
                WHERE m.slug = :main', $p);
            // Suppression du doublon (liaisons supprimées en cascade)
            $this->addSql('DELETE FROM product WHERE slug = :dup', ['dup' => $dup]);
        }

        foreach (self::SOY_SAUCES as $old => [$name, $slug]) {
            $this->addSql('UPDATE product SET nom = :nom, slug = :slug, updated_at = NOW() WHERE slug = :old',
                ['nom' => $name, 'slug' => $slug, 'old' => $old]);
        }

        foreach (self::SLUG_FIXES as $old => $new) {
            $this->addSql('UPDATE product SET slug = :new, updated_at = NOW() WHERE slug = :old', ['new' => $new, 'old' => $old]);
        }

        // Positions continues (tri dans l'admin) après suppression des doublons
        $this->addSql('SET @pos := -1');
        $this->addSql('UPDATE product SET position = (@pos := @pos + 1) ORDER BY position, id');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Fusion de fiches produits : restaurer une sauvegarde de la base si besoin.');
    }
}
