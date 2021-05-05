<?php

declare(strict_types=1);

namespace Rector\Compiler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\ShellCode;

final class DowngradePathsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('[DEV] Provide vendor paths that require downgrade to required PHP version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $downgradePaths = $this->findVendorAndRulePaths();

        $downgradePaths = array_merge([
            // must be separated to cover container get() trait + psr container contract get()
            'config',
            'rules',
            'bin src packages rector.php',
        ], $downgradePaths);

        // bash format
        $downgradePathsLine = implode(';', $downgradePaths);
        echo $downgradePathsLine . PHP_EOL;

        return ShellCode::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function findVendorAndRulePaths(): array
    {
        $finder = (new Finder())->directories()
            ->in(__DIR__ . '/../../../..')
            ->depth(2)
            ->path('#(vendor)\/#')
            ->sortByName();

        $directoryPaths = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $directoryPaths[] = $fileInfo->getRelativePath();
        }

        $directoryPaths = array_unique($directoryPaths);
        return array_values($directoryPaths);
    }
}
