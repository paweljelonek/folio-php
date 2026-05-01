<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'assets:link',
    description: 'Creates a symlink from a public directory to an assets source directory',
)]
final class AssetsLinkCommand extends Command
{
    public function __construct(private readonly string $projectRoot)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Source assets directory (relative to project root)', 'examples/assets')
            ->addOption('target', null, InputOption::VALUE_REQUIRED, 'Symlink path to create (relative to project root)', 'public/assets')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Remove existing symlink and recreate it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $source = $this->projectRoot . '/' . ltrim((string) $input->getOption('source'), '/');
        $target = $this->projectRoot . '/' . ltrim((string) $input->getOption('target'), '/');

        if (!is_dir($source)) {
            $io->error(sprintf('Source directory not found: %s', $source));
            return Command::FAILURE;
        }

        if (is_link($target)) {
            if (!$input->getOption('force')) {
                $io->warning(sprintf('Symlink already exists: %s → %s', $target, readlink($target)));
                $io->comment('Use --force to recreate it.');
                return Command::SUCCESS;
            }
            unlink($target);
        } elseif (file_exists($target)) {
            $io->error(sprintf('Target path exists and is not a symlink: %s', $target));
            return Command::FAILURE;
        }

        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $relativeSource = $this->relativeTarget($targetDir, $source);

        if (!symlink($relativeSource, $target)) {
            $io->error('Failed to create symlink. Check directory permissions.');
            return Command::FAILURE;
        }

        $io->success(sprintf('Symlink created: %s → %s', $target, $relativeSource));
        return Command::SUCCESS;
    }

    private function relativeTarget(string $fromDir, string $toPath): string
    {
        $from = explode('/', rtrim(realpath($fromDir) ?: $fromDir, '/'));
        $to   = explode('/', rtrim(realpath($toPath)  ?: $toPath,  '/'));

        while ($from && $to && $from[0] === $to[0]) {
            array_shift($from);
            array_shift($to);
        }

        return str_repeat('../', count($from)) . implode('/', $to);
    }
}
