<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408102537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A45BDDC1989D9B62 ON application (slug)');
        $this->addSql('ALTER TABLE blog_post ADD slug VARCHAR(191) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA5AE01D989D9B62 ON blog_post (slug)');
        $this->addSql('ALTER TABLE family ADD slug VARCHAR(191) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E6215B989D9B62 ON family (slug)');
        $this->addSql('ALTER TABLE product ADD slug VARCHAR(191) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD989D9B62 ON product (slug)');
        $this->addSql('ALTER TABLE type ADD slug VARCHAR(191) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8CDE5729989D9B62 ON type (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A45BDDC1989D9B62 ON application');
        $this->addSql('DROP INDEX UNIQ_BA5AE01D989D9B62 ON blog_post');
        $this->addSql('ALTER TABLE blog_post DROP slug');
        $this->addSql('DROP INDEX UNIQ_A5E6215B989D9B62 ON family');
        $this->addSql('ALTER TABLE family DROP slug');
        $this->addSql('DROP INDEX UNIQ_D34A04AD989D9B62 ON product');
        $this->addSql('ALTER TABLE product DROP slug');
        $this->addSql('DROP INDEX UNIQ_8CDE5729989D9B62 ON type');
        $this->addSql('ALTER TABLE type DROP slug');
    }
}
