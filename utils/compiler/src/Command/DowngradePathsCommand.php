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

        foreach ($downgradePaths as $key => $downgradePath) {
            if (in_array(
                $downgradePath,
                ['vendor/symplify', 'vendor/symfony', 'vendor/nikic', 'vendor/psr', 'vendor/phpstan'],
                true
            )) {
                unset($downgradePaths[$key]);
            }
        }

        $downgradePaths = array_merge([
            // must be separated to cover container get() trait + psr container contract get()
            'config',
            'vendor/phpstan/phpdoc-parser/src',
            'vendor/symfony/error-handler',
            'vendor/symfony/dependency-injection',
            'vendor/symfony/console',
            'vendor/symfony vendor/psr',
            'vendor/symplify vendor/nikic bin src packages rector.php',
            'rules',
        ], $downgradePaths);

        if (file_exists(getcwd() . '/vendor/phpstan/phpstan-extracted/vendor')) {
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/phpstan/phpdoc-parser/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/ondrejmirtes/better-reflection/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/di/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/php-generator/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/utils/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/schema/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/finder/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/nette/robot-loader/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/ondram/ci-detector/src';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/symfony/finder';
            $downgradePaths[] = 'vendor/phpstan/phpstan-extracted/vendor/symfony/console';
        }

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
