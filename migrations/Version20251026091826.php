<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026091826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_packaging (product_id INT NOT NULL, packaging_id INT NOT NULL, INDEX IDX_D12BBB084584665A (product_id), INDEX IDX_D12BBB084E7B3801 (packaging_id), PRIMARY KEY(product_id, packaging_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_packaging ADD CONSTRAINT FK_D12BBB084584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_packaging ADD CONSTRAINT FK_D12BBB084E7B3801 FOREIGN KEY (packaging_id) REFERENCES packaging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP packaging');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_packaging DROP FOREIGN KEY FK_D12BBB084584665A');
        $this->addSql('ALTER TABLE product_packaging DROP FOREIGN KEY FK_D12BBB084E7B3801');
        $this->addSql('DROP TABLE product_packaging');
        $this->addSql('ALTER TABLE product ADD packaging VARCHAR(255) DEFAULT NULL');
    }
}
