<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les descriptions produits depuis un CSV (colonnes « slug » et « description »).
 *
 * Le CSV peut venir directement d'Excel (« Enregistrer sous > CSV UTF-8 », séparateur ; ou ,).
 * Une colonne « valide » optionnelle permet de n'importer que les lignes validées (oui/x/1).
 * Les paragraphes sont séparés par une ligne vide dans la cellule.
 *
 *   php bin/console app:products:import-descriptions deploy/descriptions.csv --dry-run
 *   php bin/console app:products:import-descriptions deploy/descriptions.csv
 *   php bin/console app:products:import-descriptions deploy/descriptions.csv --sql=deploy/descriptions.sql
 */
#[AsCommand(name: 'app:products:import-descriptions', description: 'Importe les descriptions produits depuis un fichier CSV')]
class ImportProductDescriptionsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv', InputArgument::REQUIRED, 'Fichier CSV (slug ; description [; valide])')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace aussi les descriptions déjà renseignées')
            ->addOption('only-validated', null, InputOption::VALUE_NONE, 'N\'importe que les lignes dont la colonne « valide » vaut oui / x / 1')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche ce qui serait modifié sans rien enregistrer')
            ->addOption('sql', null, InputOption::VALUE_REQUIRED, 'Écrit les UPDATE dans ce fichier SQL (pour phpMyAdmin) au lieu de modifier la base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = $this->readCsv($input->getArgument('csv'), $io);
        if ($rows === null) {
            return Command::FAILURE;
        }

        $repo = $this->em->getRepository(Product::class);
        $sql = [];
        $stats = ['mis à jour' => 0, 'déjà renseignés (ignorés)' => 0, 'non validés (ignorés)' => 0, 'slug inconnu' => 0, 'vides' => 0];

        foreach ($rows as $line => $row) {
            $slug = trim($row['slug'] ?? '');
            $text = $this->normalize($row['description'] ?? '');
            if ($slug === '' || $text === '') {
                $stats['vides']++;
                continue;
            }
            if ($input->getOption('only-validated') && !\in_array(mb_strtolower(trim($row['valide'] ?? '')), ['oui', 'x', '1', 'ok', 'yes'], true)) {
                $stats['non validés (ignorés)']++;
                continue;
            }
            $product = $repo->findOneBy(['slug' => $slug]);
            if (!$product) {
                $io->warning(sprintf('Ligne %d : produit « %s » introuvable', $line, $slug));
                $stats['slug inconnu']++;
                continue;
            }
            if ($product->getDescription() && !$input->getOption('overwrite')) {
                $stats['déjà renseignés (ignorés)']++;
                continue;
            }
            $stats['mis à jour']++;
            if ($input->getOption('sql')) {
                $conn = $this->em->getConnection();
                $sql[] = sprintf('UPDATE product SET description = %s, updated_at = NOW() WHERE slug = %s%s;',
                    $conn->quote($text), $conn->quote($slug),
                    $input->getOption('overwrite') ? '' : " AND (description IS NULL OR description = '')");
            } elseif (!$input->getOption('dry-run')) {
                $product->setDescription($text);
            }
        }

        if ($input->getOption('sql')) {
            file_put_contents($input->getOption('sql'), "SET NAMES utf8mb4;\n" . implode("\n", $sql) . "\n");
            $io->success(sprintf('%d UPDATE écrits dans %s', \count($sql), $input->getOption('sql')));
        } elseif (!$input->getOption('dry-run')) {
            $this->em->flush();
        }

        $io->table(['Résultat', 'Nombre'], array_map(null, array_keys($stats), array_values($stats)));
        if ($input->getOption('dry-run')) {
            $io->note('Simulation : rien n\'a été enregistré.');
        }

        return Command::SUCCESS;
    }

    /** @return array<int, array<string, string>>|null */
    private function readCsv(string $path, SymfonyStyle $io): ?array
    {
        if (!is_readable($path)) {
            $io->error("Fichier introuvable : $path");
            return null;
        }
        $content = file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // BOM d'Excel
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252'); // CSV Excel « non UTF-8 »
        }
        $firstLine = strtok($content, "\n");
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);
        $header = array_map(fn ($h) => mb_strtolower(trim($h)), fgetcsv($handle, 0, $delimiter, '"', '') ?: []);
        if (!\in_array('slug', $header, true) || !\in_array('description', $header, true)) {
            $io->error('Le CSV doit contenir les colonnes « slug » et « description ».');
            return null;
        }
        $rows = [];
        $line = 1;
        while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $line++;
            if ($data === [null]) {
                continue;
            }
            $rows[$line] = array_combine($header, array_pad(\array_slice($data, 0, \count($header)), \count($header), ''));
        }
        fclose($handle);

        return $rows;
    }

    /** Uniformise les fins de ligne et les paragraphes (séparés par une ligne vide). */
    private function normalize(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return preg_replace('/[ \t]+/', ' ', $text);
    }
}
