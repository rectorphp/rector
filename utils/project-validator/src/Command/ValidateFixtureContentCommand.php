<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\EasyTesting\ValueObject\SplitLine;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ValidateFixtureContentCommand extends Command
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
        $this->setDescription('[CI] Validate tests fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hasError = false;

        foreach ($this->getFixtureFileInfos() as $fixtureFileInfo) {
            if (! $this->hasFileIdenticalCodeBeforeAndAfter($fixtureFileInfo)) {
                continue;
            }

            // files content is equal, but it should not
            $message = sprintf(
                'The "%s" file has same content before and after. Remove the 2nd half of the file.',
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
    private function getFixtureFileInfos(): array
    {
        $finder = (new Finder())
            ->files()
            ->name('*.php.inc')
            ->in(__DIR__ . '/../../../../tests')
            ->in(__DIR__ . '/../../../../packages/*/tests')
            ->in(__DIR__ . '/../../../../rules/*/tests');

        return $this->finderSanitizer->sanitize($finder);
    }

    private function hasFileIdenticalCodeBeforeAndAfter(SmartFileInfo $smartFileInfo): bool
    {
        if (! Strings::match($smartFileInfo->getContents(), SplitLine::SPLIT_LINE)) {
            return false;
        }

        // original → expected
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($smartFileInfo);

        return $inputAndExpected->getInput() === $inputAndExpected->getExpected();
    }
}
