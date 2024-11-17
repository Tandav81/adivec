<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117135519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE products_application (product_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_5E86BF5A4584665A (product_id), INDEX IDX_5E86BF5A3E030ACD (application_id), PRIMARY KEY(product_id, application_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE products_application ADD CONSTRAINT FK_5E86BF5A4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products_application ADD CONSTRAINT FK_5E86BF5A3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP application');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products_application DROP FOREIGN KEY FK_5E86BF5A4584665A');
        $this->addSql('ALTER TABLE products_application DROP FOREIGN KEY FK_5E86BF5A3E030ACD');
        $this->addSql('DROP TABLE products_application');
        $this->addSql('ALTER TABLE product ADD application VARCHAR(255) DEFAULT NULL');
    }
}
