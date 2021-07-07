<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\ShowOutputFormatterCollector;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

final class ShowCommand extends Command
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private OutputStyleInterface $outputStyle,
        private ShowOutputFormatterCollector $showOutputFormatterCollector,
        private array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show loaded Rectors with their configuration');

        $names = $this->showOutputFormatterCollector->getNames();

        $description = sprintf('Select output format: "%s".', implode('", "', $names));
        $this->addOption(
            Option::OUTPUT_FORMAT,
            'o',
            InputOption::VALUE_OPTIONAL,
            $description,
            ConsoleOutputFormatter::NAME
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputFormat = (string) $input->getOption(Option::OUTPUT_FORMAT);

        $this->reportLoadedRectors($outputFormat);

        return ShellCode::SUCCESS;
    }

    private function reportLoadedRectors(string $outputFormat): void
    {
        $rectors = array_filter(
            $this->rectors,
            function (RectorInterface $rector): bool {
                if ($rector instanceof PostRectorInterface) {
                    return false;
                }

                return ! $rector instanceof ComplementaryRectorInterface;
            }
        );

        $rectorCount = count($rectors);

        if ($rectorCount === 0) {
            $warningMessage = sprintf(
                'No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.',
                PHP_EOL . PHP_EOL,
                PHP_EOL
            );
            $this->outputStyle->warning($warningMessage);

            return;
        }

        $outputFormatter = $this->showOutputFormatterCollector->getByName($outputFormat);
        $outputFormatter->list($rectors);
    }
}
