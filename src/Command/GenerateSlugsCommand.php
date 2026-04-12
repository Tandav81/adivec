<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-slugs',
    description: 'Génère les slugs manquants (écrit directement en base, sans passer par Doctrine ORM)',
)]
class GenerateSlugsCommand extends Command
{
    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Génération des slugs manquants (via SQL direct)');

        $entities = [
            'Product'     => ['table' => 'product',     'field' => 'nom'],
            'BlogPost'    => ['table' => 'blog_post',   'field' => 'title'],
            'Family'      => ['table' => 'family',      'field' => 'name'],
            'Type'        => ['table' => 'type',        'field' => 'name'],
            'Application' => ['table' => 'application', 'field' => 'libelle'],
        ];

        foreach ($entities as $label => $config) {
            $table = $config['table'];
            $field = $config['field'];

            // Récupère les slugs déjà en base pour éviter les doublons
            $existing = $this->connection->fetchFirstColumn(
                "SELECT slug FROM $table WHERE slug IS NOT NULL"
            );
            $usedSlugs = array_flip($existing);

            // Récupère les lignes sans slug
            $rows = $this->connection->fetchAllAssociative(
                "SELECT id, $field FROM $table WHERE slug IS NULL OR slug = ''"
            );

            $count = 0;
            foreach ($rows as $row) {
                $base = $this->slugify((string) $row[$field]) ?: 'item-' . $row['id'];
                $slug = $base;
                $i = 1;
                while (isset($usedSlugs[$slug])) {
                    $slug = $base . '-' . $i++;
                }
                $usedSlugs[$slug] = true;

                $this->connection->executeStatement(
                    "UPDATE $table SET slug = ? WHERE id = ?",
                    [$slug, $row['id']]
                );
                $count++;
            }

            $io->success(sprintf('%s : %d slug(s) généré(s)', $label, $count));
        }

        return Command::SUCCESS;
    }
}
