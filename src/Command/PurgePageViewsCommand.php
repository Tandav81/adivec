<?php

namespace App\Command;

use App\Repository\PageViewRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Purge les vues de pages anciennes pour éviter que la table page_view grossisse indéfiniment.
 *
 * À lancer par cron, par exemple chaque nuit :
 *   0 3 * * * cd /chemin/vers/adivec && php bin/console app:pageview:purge --days=365
 */
#[AsCommand(
    name: 'app:pageview:purge',
    description: 'Supprime les vues de pages plus anciennes que N jours (365 par défaut).',
)]
class PurgePageViewsCommand extends Command
{
    public function __construct(private readonly PageViewRepository $pageViewRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Nombre de jours d\'historique à conserver', 365);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = max(1, (int) $input->getOption('days'));

        $deleted = $this->pageViewRepository->deleteOlderThan($days);
        $io->success(sprintf('%d vue(s) de page supprimée(s) (plus anciennes que %d jours).', $deleted, $days));

        return Command::SUCCESS;
    }
}
