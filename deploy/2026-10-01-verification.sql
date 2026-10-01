-- Adivec – état de la base après la migration du 01/10/2026 (lecture seule, ne modifie rien)
-- Chaque ligne indique la valeur attendue si la migration est complète.
SELECT 'produits (attendu 108)' AS controle, (SELECT COUNT(*) FROM product) AS valeur
UNION ALL SELECT 'doublons -1 restants (attendu 0)', (SELECT COUNT(*) FROM product WHERE slug IN ('amidon-natif-de-ble-1','gomme-de-guar-1','gomme-de-xanthane-1','fibre-de-pomme-de-terre-1','cire-d-abeille-1','cire-de-candelilla-1','cire-de-carnauba-1'))
UNION ALL SELECT 'catégories secondaires (attendu 7)', (SELECT COUNT(*) FROM product_secondary_type)
UNION ALL SELECT 'sauces déshydratées renommées (attendu 3)', (SELECT COUNT(*) FROM product WHERE slug LIKE '%-deshydratee')
UNION ALL SELECT 'anciens slugs à accents (attendu 0)', (SELECT COUNT(*) FROM product WHERE slug IN ('carot-ene','lut-eine','r-eglisse','prot-eine-de-bl-e','l-ecithines-de-soja','sauce-soja-umami-elev-e','sauce-soja-sucr-ee'))
UNION ALL SELECT 'news sans date (attendu 0)', (SELECT COUNT(*) FROM blog_post WHERE created_at IS NULL)
UNION ALL SELECT 'positions continues (attendu 1)', (SELECT COUNT(DISTINCT position) = COUNT(*) AND MAX(position) = COUNT(*) - 1 FROM product)
UNION ALL SELECT 'table blog_post_product existe (attendu 1)', (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'blog_post_product')
UNION ALL SELECT 'clés étrangères product_secondary_type (attendu 2)', (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'product_secondary_type')
UNION ALL SELECT 'clés étrangères blog_post_product (attendu 2)', (SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'blog_post_product')
UNION ALL SELECT 'migrations du 01/10 enregistrées (attendu 5)', (SELECT COUNT(*) FROM doctrine_migration_versions WHERE version IN ('DoctrineMigrations\\Version20260412000000','DoctrineMigrations\\Version20261001090000','DoctrineMigrations\\Version20261001120000','DoctrineMigrations\\Version20261001120100','DoctrineMigrations\\Version20261001130000'));
