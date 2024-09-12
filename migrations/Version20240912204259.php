<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912204259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type DROP FOREIGN KEY FK_8CDE5729C35E566A');
        $this->addSql('DROP INDEX IDX_8CDE5729C35E566A ON type');
        $this->addSql('ALTER TABLE type DROP family');
        $this->addSql('ALTER TABLE type ADD CONSTRAINT FK_8CDE5729C35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('CREATE INDEX IDX_8CDE5729C35E566A ON type (family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type DROP FOREIGN KEY FK_8CDE5729C35E566A');
        $this->addSql('DROP INDEX IDX_8CDE5729C35E566A ON type');
        $this->addSql('ALTER TABLE type ADD family INT NOT NULL');
        $this->addSql('ALTER TABLE type ADD CONSTRAINT FK_8CDE5729C35E566A FOREIGN KEY (family) REFERENCES family (id)');
        $this->addSql('CREATE INDEX IDX_8CDE5729C35E566A ON type (family)');
    }
}
