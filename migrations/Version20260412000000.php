<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table page_view pour le tracking des visites';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE page_view (
            id INT AUTO_INCREMENT NOT NULL,
            url VARCHAR(191) NOT NULL,
            visited_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ip_hash VARCHAR(64) DEFAULT NULL,
            user_agent VARCHAR(191) DEFAULT NULL,
            referer VARCHAR(191) DEFAULT NULL,
            INDEX visited_at_idx (visited_at),
            INDEX url_idx (url),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE page_view');
    }
}
