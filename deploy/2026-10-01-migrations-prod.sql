-- =====================================================================
-- Adivec – mise à jour SEO du 01/10/2026 : migrations Doctrine en SQL brut
-- À exécuter UNE FOIS dans phpMyAdmin sur la base de production,
-- APRÈS une sauvegarde (Exporter) et APRÈS avoir déployé le code.
-- Équivaut à : php bin/console doctrine:migrations:migrate
-- (à utiliser seulement si la console Symfony n'est pas disponible en prod)
-- =====================================================================
SET NAMES utf8mb4;
START TRANSACTION;

CREATE TABLE IF NOT EXISTS page_view ( id INT AUTO_INCREMENT NOT NULL, url VARCHAR(191) NOT NULL, visited_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ip_hash VARCHAR(64) DEFAULT NULL, user_agent VARCHAR(191) DEFAULT NULL, referer VARCHAR(191) DEFAULT NULL, INDEX visited_at_idx (visited_at), INDEX url_idx (url), PRIMARY KEY(id) ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
UPDATE blog_post SET created_at = '2025-02-17 09:37:35' WHERE slug = 'etude-UE' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-05-07 14:54:13' WHERE slug = 'sauce-soja' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-10-10 08:27:41' WHERE slug = 'fromage-vegan' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-10-23 06:10:09' WHERE slug = 'club-pai' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-11-27 07:33:55' WHERE slug = 'umami' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-01-28 17:25:51' WHERE slug = 'cereales' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-10-10 09:39:43' WHERE slug = 'bolognaise-vegetalienne' AND created_at IS NULL;
UPDATE blog_post SET created_at = '2025-11-26 14:53:19' WHERE slug = 'agar-agar' AND created_at IS NULL;
CREATE TABLE IF NOT EXISTS product_secondary_type (product_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_9032D544584665A (product_id), INDEX IDX_9032D54C54C8C93 (type_id), PRIMARY KEY(product_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE product_secondary_type ADD CONSTRAINT FK_9032D544584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE;
ALTER TABLE product_secondary_type ADD CONSTRAINT FK_9032D54C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE;
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'amidon-natif-de-ble-1' WHERE m.slug = 'amidon-natif-de-ble' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'amidon-natif-de-ble-1' JOIN product m ON m.slug = 'amidon-natif-de-ble';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'amidon-natif-de-ble-1' JOIN product m ON m.slug = 'amidon-natif-de-ble';
UPDATE product m JOIN product d ON d.slug = 'amidon-natif-de-ble-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'amidon-natif-de-ble';
DELETE FROM product WHERE slug = 'amidon-natif-de-ble-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'gomme-de-guar-1' WHERE m.slug = 'gomme-de-guar' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'gomme-de-guar-1' JOIN product m ON m.slug = 'gomme-de-guar';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'gomme-de-guar-1' JOIN product m ON m.slug = 'gomme-de-guar';
UPDATE product m JOIN product d ON d.slug = 'gomme-de-guar-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'gomme-de-guar';
DELETE FROM product WHERE slug = 'gomme-de-guar-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'gomme-de-xanthane-1' WHERE m.slug = 'gomme-de-xanthane' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'gomme-de-xanthane-1' JOIN product m ON m.slug = 'gomme-de-xanthane';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'gomme-de-xanthane-1' JOIN product m ON m.slug = 'gomme-de-xanthane';
UPDATE product m JOIN product d ON d.slug = 'gomme-de-xanthane-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'gomme-de-xanthane';
DELETE FROM product WHERE slug = 'gomme-de-xanthane-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'fibre-de-pomme-de-terre-1' WHERE m.slug = 'fibre-de-pomme-de-terre' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'fibre-de-pomme-de-terre-1' JOIN product m ON m.slug = 'fibre-de-pomme-de-terre';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'fibre-de-pomme-de-terre-1' JOIN product m ON m.slug = 'fibre-de-pomme-de-terre';
UPDATE product m JOIN product d ON d.slug = 'fibre-de-pomme-de-terre-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'fibre-de-pomme-de-terre';
DELETE FROM product WHERE slug = 'fibre-de-pomme-de-terre-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'cire-d-abeille-1' WHERE m.slug = 'cire-d-abeille' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'cire-d-abeille-1' JOIN product m ON m.slug = 'cire-d-abeille';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'cire-d-abeille-1' JOIN product m ON m.slug = 'cire-d-abeille';
UPDATE product m JOIN product d ON d.slug = 'cire-d-abeille-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'cire-d-abeille';
DELETE FROM product WHERE slug = 'cire-d-abeille-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'cire-de-candelilla-1' WHERE m.slug = 'cire-de-candelilla' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'cire-de-candelilla-1' JOIN product m ON m.slug = 'cire-de-candelilla';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'cire-de-candelilla-1' JOIN product m ON m.slug = 'cire-de-candelilla';
UPDATE product m JOIN product d ON d.slug = 'cire-de-candelilla-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'cire-de-candelilla';
DELETE FROM product WHERE slug = 'cire-de-candelilla-1';
INSERT IGNORE INTO product_secondary_type (product_id, type_id) SELECT m.id, d.type_id FROM product m JOIN product d ON d.slug = 'cire-de-carnauba-1' WHERE m.slug = 'cire-de-carnauba' AND d.type_id <> m.type_id;
INSERT IGNORE INTO products_application (product_id, application_id) SELECT m.id, pa.application_id FROM products_application pa JOIN product d ON d.id = pa.product_id AND d.slug = 'cire-de-carnauba-1' JOIN product m ON m.slug = 'cire-de-carnauba';
INSERT IGNORE INTO product_packaging (product_id, packaging_id) SELECT m.id, pp.packaging_id FROM product_packaging pp JOIN product d ON d.id = pp.product_id AND d.slug = 'cire-de-carnauba-1' JOIN product m ON m.slug = 'cire-de-carnauba';
UPDATE product m JOIN product d ON d.slug = 'cire-de-carnauba-1' SET m.fournisseur_id = COALESCE(m.fournisseur_id, d.fournisseur_id), m.description = COALESCE(m.description, d.description), m.updated_at = NOW() WHERE m.slug = 'cire-de-carnauba';
DELETE FROM product WHERE slug = 'cire-de-carnauba-1';
UPDATE product SET nom = 'Sauce soja standard déshydratée', slug = 'sauce-soja-standard-deshydratee', updated_at = NOW() WHERE slug = 'sauce-soja-standard-1';
UPDATE product SET nom = 'Sauce soja sans gluten (Tamari) déshydratée', slug = 'sauce-soja-sans-gluten-tamari-deshydratee', updated_at = NOW() WHERE slug = 'sauce-soja-sans-gluten-tamari-1';
UPDATE product SET nom = 'Sauce soja Umami élevé déshydratée', slug = 'sauce-soja-umami-eleve-deshydratee', updated_at = NOW() WHERE slug = 'sauce-soja-umami-elev-e-1';
UPDATE product SET slug = 'sauce-soja-umami-eleve', updated_at = NOW() WHERE slug = 'sauce-soja-umami-elev-e';
UPDATE product SET slug = 'carotene', updated_at = NOW() WHERE slug = 'carot-ene';
UPDATE product SET slug = 'concentrat-de-proteine-de-pois', updated_at = NOW() WHERE slug = 'concentrat-de-prot-eine-de-pois';
UPDATE product SET slug = 'exhausteur-de-gout-sauce-soja-sans-soja', updated_at = NOW() WHERE slug = 'exhausteur-de-go-ut-sauce-soja-sans-soja';
UPDATE product SET slug = 'isolat-de-proteine-de-pois', updated_at = NOW() WHERE slug = 'isolat-de-prot-eine-de-pois';
UPDATE product SET slug = 'lecithine-de-soja-bio', updated_at = NOW() WHERE slug = 'l-ecithine-de-soja-bio';
UPDATE product SET slug = 'lecithine-de-tournesol-bio', updated_at = NOW() WHERE slug = 'l-ecithine-de-tournesol-bio';
UPDATE product SET slug = 'lecithines-de-colza', updated_at = NOW() WHERE slug = 'l-ecithines-de-colza';
UPDATE product SET slug = 'lecithines-de-soja', updated_at = NOW() WHERE slug = 'l-ecithines-de-soja';
UPDATE product SET slug = 'lecithines-de-tournesol', updated_at = NOW() WHERE slug = 'l-ecithines-de-tournesol';
UPDATE product SET slug = 'luteine', updated_at = NOW() WHERE slug = 'lut-eine';
UPDATE product SET slug = 'proteine-de-ble', updated_at = NOW() WHERE slug = 'prot-eine-de-bl-e';
UPDATE product SET slug = 'reglisse', updated_at = NOW() WHERE slug = 'r-eglisse';
UPDATE product SET slug = 'sauce-soja-a-teneur-reduite-en-sel', updated_at = NOW() WHERE slug = 'sauce-soja-a-teneur-r-eduite-en-sel';
UPDATE product SET slug = 'sauce-soja-sucree', updated_at = NOW() WHERE slug = 'sauce-soja-sucr-ee';
UPDATE product SET slug = 'sauce-soja-taux-de-sel-reduit', updated_at = NOW() WHERE slug = 'sauce-soja-taux-de-sel-r-eduit';
SET @pos := -1;
UPDATE product SET position = (@pos := @pos + 1) ORDER BY position, id;
CREATE TABLE IF NOT EXISTS blog_post_product (blog_post_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_B47E5C19A77FBEAF (blog_post_id), INDEX IDX_B47E5C194584665A (product_id), PRIMARY KEY(blog_post_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE blog_post_product ADD CONSTRAINT FK_B47E5C19A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE;
ALTER TABLE blog_post_product ADD CONSTRAINT FK_B47E5C194584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE;
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidon-natif-de-pois' WHERE b.slug = 'etude-UE';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'isolat-de-proteine-de-pois' WHERE b.slug = 'etude-UE';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'fibre-de-pois' WHERE b.slug = 'etude-UE';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidons-modifies-de-pois' WHERE b.slug = 'etude-UE';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'lecithines-de-soja' WHERE b.slug = 'sauce-soja';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'lecithine-de-soja-bio' WHERE b.slug = 'sauce-soja';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'sauce-soja-standard' WHERE b.slug = 'sauce-soja';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'sauce-soja-bio' WHERE b.slug = 'sauce-soja';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'isolat-de-proteine-de-pois' WHERE b.slug = 'fromage-vegan';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidons-modifies-de-pomme-de-terre-clean-label' WHERE b.slug = 'fromage-vegan';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidons-modifies-de-pois-clean-label' WHERE b.slug = 'fromage-vegan';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'sauce-soja-standard' WHERE b.slug = 'umami';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'sauce-soja-umami-eleve' WHERE b.slug = 'umami';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'sauce-soja-umami-eleve-deshydratee' WHERE b.slug = 'umami';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'marinade-teriyaki' WHERE b.slug = 'umami';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidons-modifies-de-pomme-de-terre-clean-label' WHERE b.slug = 'des-solutions-clean-label-pensees-pour-les-applications-reelles-texture-stabilite-et-simplicite-detiquetage';
INSERT IGNORE INTO blog_post_product (blog_post_id, product_id) SELECT b.id, p.id FROM blog_post b JOIN product p ON p.slug = 'amidons-modifies-de-pois-clean-label' WHERE b.slug = 'des-solutions-clean-label-pensees-pour-les-applications-reelles-texture-stabilite-et-simplicite-detiquetage';

-- Enregistrement des migrations comme exécutées
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\Version20260412000000', NOW(), 0);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\Version20261001090000', NOW(), 0);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\Version20261001120000', NOW(), 0);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\Version20261001120100', NOW(), 0);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\\Version20261001130000', NOW(), 0);

COMMIT;
