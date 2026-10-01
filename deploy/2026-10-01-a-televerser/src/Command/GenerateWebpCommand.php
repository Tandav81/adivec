<?php

namespace App\Command;

use App\Service\WebpGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:images:webp',
    description: 'Génère les versions WebP (image.jpg.webp) manquantes dans public/img et public/uploads/images',
)]
class GenerateWebpCommand extends Command
{
    public function __construct(private readonly WebpGenerator $generator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Régénère aussi les WebP existants');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$this->generator->isSupported()) {
            $io->error('Ni GD (avec WebP) ni Imagick ne sont disponibles sur ce PHP.');
            return Command::FAILURE;
        }

        $result = $this->generator->generateMissing((bool) $input->getOption('force'), function (string $file, string $status) use ($io) {
            if ($status !== 'skipped' && $io->isVerbose()) {
                $io->writeln(sprintf('%s %s', $status === 'created' ? '<info>✔</info>' : '<error>✘</error>', $file));
            }
        });

        $io->success(sprintf('%d WebP créés, %d inchangés.', $result['created'], $result['skipped']));
        if ($result['failed']) {
            $io->warning("Échecs (image trop grande ou illisible) :\n" . implode("\n", $result['failed']));
        }

        return Command::SUCCESS;
    }
}
