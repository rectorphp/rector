<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ShowCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ActiveRectorsProvider
     */
    private $activeRectorsProvider;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ActiveRectorsProvider $activeRectorsProvider,
        ParameterProvider $parameterProvider
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->activeRectorsProvider = $activeRectorsProvider;
        $this->parameterProvider = $parameterProvider;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportLoadedRectors();
        $this->reportLoadedSets();

        return ShellCode::SUCCESS;
    }

    private function reportLoadedRectors(): void
    {
        $activeRectors = $this->activeRectorsProvider->provide();

        $rectorCount = count($activeRectors);

        if ($rectorCount > 0) {
            $this->symfonyStyle->title('Loaded Rector rules');

            foreach ($activeRectors as $rector) {
                $this->symfonyStyle->writeln(' * ' . get_class($rector));
            }

            $message = sprintf('%d loaded Rectors', $rectorCount);
            $this->symfonyStyle->success($message);
        } else {
            $warningMessage = sprintf(
                'No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.',
                PHP_EOL . PHP_EOL,
                PHP_EOL
            );
            $this->symfonyStyle->warning($warningMessage);
        }
    }

    private function reportLoadedSets(): void
    {
        $sets = (array) $this->parameterProvider->provideParameter(Option::SETS);
        if ($sets === []) {
            return;
        }

        $this->symfonyStyle->newLine(2);
        $this->symfonyStyle->title('Loaded Sets');

        sort($sets);

        $setFilePaths = [];
        foreach ($sets as $set) {
            $setFileInfo = new SmartFileInfo($set);
            $setFilePaths[] = $setFileInfo->getRelativeFilePathFromCwd();
        }

        $this->symfonyStyle->listing($setFilePaths);

        $message = sprintf('%d loaded sets', count($sets));
        $this->symfonyStyle->success($message);
    }
}
