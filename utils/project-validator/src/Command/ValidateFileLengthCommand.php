<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Command;

use Rector\Utils\ProjectValidator\Finder\ProjectFilesFinder;
use Rector\Utils\ProjectValidator\TooLongFilesResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;

final class ValidateFileLengthCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ProjectFilesFinder
     */
    private $projectFilesFinder;

    /**
     * @var TooLongFilesResolver
     */
    private $tooLongFilesResolver;

    public function __construct(
        ProjectFilesFinder $projectFilesFinder,
        SymfonyStyle $symfonyStyle,
        TooLongFilesResolver $tooLongFilesResolver
    ) {
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();

        $this->projectFilesFinder = $projectFilesFinder;
        $this->tooLongFilesResolver = $tooLongFilesResolver;
    }

    protected function configure(): void
    {
        $this->setDescription('[CI] Make sure the file path length are not breaking normal Windows max length');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileInfos = $this->projectFilesFinder->find();
        $tooLongFileInfos = $this->tooLongFilesResolver->resolve($fileInfos);

        if ($tooLongFileInfos === []) {
            $message = sprintf('Checked %d files - all fit max file length', count($fileInfos));
            $this->symfonyStyle->success($message);

            return ShellCode::SUCCESS;
        }

        foreach ($tooLongFileInfos as $tooLongFileInfo) {
            $message = sprintf(
                'Paths for file "%s" has %d chars, but must be shorter than %d.',
                $tooLongFileInfo->getRealPath(),
                strlen($tooLongFileInfo->getRealPath()),
                TooLongFilesResolver::MAX_FILE_LENGTH
            );

            $this->symfonyStyle->warning($message);
        }

        return ShellCode::ERROR;
    }
}
