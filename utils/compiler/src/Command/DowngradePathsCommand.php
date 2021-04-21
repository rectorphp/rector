<?php

declare(strict_types=1);

namespace Rector\Compiler\Command;

use Nette\Utils\Strings;
use Rector\Compiler\Downgrade\WhyNotVendorPackagesResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\ShellCode;

final class DowngradePathsCommand extends Command
{
    /**
     * @var string
     */
    private const OPTION_PHP_VERSION = 'PHP_VERSION';

    /**
     * @var WhyNotVendorPackagesResolver
     */
    private $whyNotVendorPackagesResolver;

    public function __construct(WhyNotVendorPackagesResolver $whyNotVendorPackagesResolver)
    {
        $this->whyNotVendorPackagesResolver = $whyNotVendorPackagesResolver;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('[DEV] Provide vendor paths that require downgrade to required PHP version');
        $this->addArgument(self::OPTION_PHP_VERSION, InputArgument::REQUIRED, 'Target PHP version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $targetPhpVersion = (string) $input->getArgument(self::OPTION_PHP_VERSION);

        $downgradePaths = $this->whyNotVendorPackagesResolver->resolveFromPhpVersion($targetPhpVersion);

        $downgradePaths = array_values($downgradePaths);

        $rulesPaths = $this->resolveRulesPaths();
        $downgradePaths = array_merge($downgradePaths, $rulesPaths);

        // make symplify grouped into 1 directory, to make covariance downgrade work with all dependent classes
        $rulesPaths = $this->resolveRulesPaths();
        $downgradePaths = array_merge($downgradePaths, $rulesPaths);

        // make symplify grouped into 1 directory, to make covariance downgrade work with all dependent classes
        foreach ($downgradePaths as $key => $downgradePath) {
            if (Strings::startsWith($downgradePath, 'vendor/symplify')) {
                unset($downgradePaths[$key]);
            }

            if (Strings::startsWith($downgradePath, 'vendor/symfony')) {
                unset($downgradePaths[$key]);
            }

            if (Strings::startsWith($downgradePath, 'vendor/nikic')) {
                unset($downgradePaths[$key]);
            }

            if (Strings::startsWith($downgradePath, 'vendor/psr')) {
                unset($downgradePaths[$key]);
            }

            if (Strings::startsWith($downgradePath, 'vendor/symfony/service-contracts')) {
                unset($downgradePaths[$key]);
            }
        }

        $downgradePaths = array_merge(['vendor/symplify vendor/symfony vendor/psr vendor/nikic src packages vendor/symfony/service-contracts'],
            $downgradePaths
        );
        $downgradePaths = array_values($downgradePaths);

        // bash format
        $downgradePathsLine = implode(';', $downgradePaths);

        echo $downgradePathsLine . PHP_EOL;

        return ShellCode::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function resolveRulesPaths(): array
    {
        $finder = new Finder();
        $finder->directories()
            ->depth(0)
            ->in(__DIR__ . '/../../../../rules')
            ->sortByName();

        $rulesPaths = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $rulesPaths[] = 'rules/' . $fileInfo->getRelativePathname();
        }

        return $rulesPaths;
    }
}
