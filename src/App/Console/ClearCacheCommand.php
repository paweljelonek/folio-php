<?php

declare(strict_types=1);

namespace App\Console;

use App\Cache\CacheInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cache:clear',
    description: 'Clears all cached rendered pages',
)]
final class ClearCacheCommand extends Command
{
    public function __construct(private readonly CacheInterface $cache)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clearing page cache');

        try {
            $this->cache->clear();
            $io->success('Cache cleared successfully.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
