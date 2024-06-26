<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626072108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE logo_partenaire (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD type_id INT NOT NULL, DROP type');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADC54C8C93 ON product (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE logo_partenaire');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC54C8C93');
        $this->addSql('DROP INDEX IDX_D34A04ADC54C8C93 ON product');
        $this->addSql('ALTER TABLE product ADD type VARCHAR(255) NOT NULL, DROP type_id');
    }
}
