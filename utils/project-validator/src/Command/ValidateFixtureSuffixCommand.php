<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ValidateFixtureSuffixCommand extends Command
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(FinderSanitizer $finderSanitizer, SymfonyStyle $symfonyStyle)
    {
        $this->finderSanitizer = $finderSanitizer;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[CI] Validate tests fixtures suffix');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hasError = false;

        foreach ($this->getInvalidFixtureFileInfos() as $fixtureFileInfo) {
            // files content is equal, but it should not
            $message = sprintf(
                'The "%s" file has invalid suffix. Should be *.php.inc',
                $fixtureFileInfo->getRelativeFilePathFromCwd()
            );

            $this->symfonyStyle->error($message);
            $hasError = true;
        }

        if ($hasError) {
            return ShellCode::ERROR;
        }

        $this->symfonyStyle->success('All fixtures are correct');

        return ShellCode::SUCCESS;
    }

    /**
     * @return SmartFileInfo[]
     */
    private function getInvalidFixtureFileInfos(): array
    {
        $finder = (new Finder())
            ->files()
            ->name('*.inc')
            ->notName('*.php.inc')
            ->in(__DIR__ . '/../../../../tests')
            ->in(__DIR__ . '/../../../../packages/*/tests')
            ->in(__DIR__ . '/../../../../rules/*/tests');

        return $this->finderSanitizer->sanitize($finder);
    }
}
