<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Catégories secondaires d'un produit (un même produit listé en alimentation humaine ET en technique).
 */
final class Version20261001120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table product_secondary_type (Product::$secondaryTypes)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_secondary_type (product_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_9032D544584665A (product_id), INDEX IDX_9032D54C54C8C93 (type_id), PRIMARY KEY(product_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_secondary_type ADD CONSTRAINT FK_9032D544584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_secondary_type ADD CONSTRAINT FK_9032D54C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_secondary_type DROP FOREIGN KEY FK_9032D544584665A');
        $this->addSql('ALTER TABLE product_secondary_type DROP FOREIGN KEY FK_9032D54C54C8C93');
        $this->addSql('DROP TABLE product_secondary_type');
    }
}
