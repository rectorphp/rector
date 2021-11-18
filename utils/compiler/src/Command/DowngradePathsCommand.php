<?php

declare(strict_types=1);

namespace Rector\Compiler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class DowngradePathsCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('[DEV] Provide vendor paths that require downgrade to required PHP version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $downgradePaths = $this->findVendorAndRulePaths();

        foreach ($downgradePaths as $key => $downgradePath) {
            if (in_array(
                $downgradePath,
                [
                    'vendor/symplify',
                    'vendor/symfony',
                    'vendor/nikic',
                    'vendor/psr',
                    'vendor/phpstan',
                    'vendor/ssch',
                ],
                true
            )) {
                unset($downgradePaths[$key]);
            }
        }

        $downgradePaths = array_merge([
            // must be separated to cover container get() trait + psr container contract get()
            'config',
            'vendor/phpstan/phpdoc-parser/src',
            'vendor/symfony/dependency-injection',
            'vendor/symfony/console',
            'vendor/symfony vendor/psr',
            'vendor/symplify vendor/nikic src packages',
            'vendor/ssch',
            'rules',
            'bin rector.php',
        ], $downgradePaths);

        // bash format
        $downgradePathsLine = implode(';', $downgradePaths);
        echo $downgradePathsLine . PHP_EOL;

        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function findVendorAndRulePaths(): array
    {
        $finder = (new Finder())->directories()
            ->in(__DIR__ . '/../../../..')
            ->depth(1)
            ->path('#(vendor)\/(.*?)#')
            ->sortByName();

        $directoryPaths = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $directoryPaths[] = $fileInfo->getRelativePathname();
        }

        $directoryPaths = array_unique($directoryPaths);
        return array_values($directoryPaths);
    }
}
