<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Dates de publication des anciennes actualités (created_at vide → schema Article invalide).
 *
 * Dates déduites de l'horodatage Unix contenu dans le nom de l'image de chaque article
 * (pattern d'upload EasyAdmin [slug]-[timestamp]), validées par Tanguy le 01/10/2026.
 * N'écrase jamais une date déjà renseignée.
 */
final class Version20261001090000 extends AbstractMigration
{
    private const DATES = [
        'etude-UE' => '2025-02-17 09:37:35',
        'sauce-soja' => '2025-05-07 14:54:13',
        'fromage-vegan' => '2025-10-10 08:27:41',
        'club-pai' => '2025-10-23 06:10:09',
        'umami' => '2025-11-27 07:33:55',
        // articles masqués (même règle, pour le jour où ils seraient republiés)
        'cereales' => '2025-01-28 17:25:51',
        'bolognaise-vegetalienne' => '2025-10-10 09:39:43',
        'agar-agar' => '2025-11-26 14:53:19',
    ];

    public function getDescription(): string
    {
        return 'Renseigne created_at des actualités publiées avant l\'ajout du Timestampable';
    }

    public function up(Schema $schema): void
    {
        foreach (self::DATES as $slug => $date) {
            $this->addSql(
                'UPDATE blog_post SET created_at = :d WHERE slug = :s AND created_at IS NULL',
                ['d' => $date, 's' => $slug]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // Volontairement vide : impossible de distinguer ces dates d'une saisie ultérieure dans l'admin.
    }
}
