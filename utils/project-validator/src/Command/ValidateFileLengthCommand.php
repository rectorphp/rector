<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\Finder\SmartFinder;

final class ValidateFileLengthCommand extends Command
{
    /**
     * In windows the max-path length is 260 chars. we give a bit room for the path up to the rector project
     */
    private const MAX_FILE_LENGTH = 200;

    /**
     * @var SmartFinder
     */
    private $smartFinder;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SmartFinder $smartFinder, SymfonyStyle $symfonyStyle)
    {
        $this->smartFinder = $smartFinder;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('[CI] Make sure the file path length are not breaking normal Windows max length');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileInfos = $this->smartFinder->find([
            __DIR__ . '/../../../../packages',
            __DIR__ . '/../../../../rules',
            __DIR__ . '/../../../../src',
            __DIR__ . '/../../../../tests'
        ], '*');

        $hasError = false;
        foreach ($fileInfos as $fileInfo) {
            $filePathLength = strlen($fileInfo->getRealPath());
            if ($filePathLength < self::MAX_FILE_LENGTH) {
                continue;
            }

            $message = sprintf(
                'Paths for file "%s" has %d chars, but must be shorter than %d.',
                $fileInfo->getRealPath(),
                $filePathLength,
                self::MAX_FILE_LENGTH
            );

            $this->symfonyStyle->warning($message);
            $hasError = true;
        }

        if ($hasError) {
            return ShellCode::ERROR;
        }

        $message = sprintf('All files fits max file length of %d chars', self::MAX_FILE_LENGTH);
        $this->symfonyStyle->success($message);

        return ShellCode::SUCCESS;
    }
}
