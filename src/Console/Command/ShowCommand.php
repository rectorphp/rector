<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\ShowOutputFormatterCollector;
use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix20211020\Symfony\Component\Console\Command\Command;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface;
final class ShowCommand extends \RectorPrefix20211020\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $outputStyle;
    /**
     * @var \Rector\Core\Console\Output\ShowOutputFormatterCollector
     */
    private $showOutputFormatterCollector;
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface[]
     */
    private $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(\Rector\Core\Contract\Console\OutputStyleInterface $outputStyle, \Rector\Core\Console\Output\ShowOutputFormatterCollector $showOutputFormatterCollector, array $rectors)
    {
        $this->outputStyle = $outputStyle;
        $this->showOutputFormatterCollector = $showOutputFormatterCollector;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Show loaded Rectors with their configuration');
        $names = $this->showOutputFormatterCollector->getNames();
        $description = \sprintf('Select output format: "%s".', \implode('", "', $names));
        $this->addOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT, 'o', \RectorPrefix20211020\Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, $description, \Rector\ChangesReporting\Output\ConsoleOutputFormatter::NAME);
    }
    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute($input, $output) : int
    {
        $outputFormat = (string) $input->getOption(\Rector\Core\Configuration\Option::OUTPUT_FORMAT);
        $this->reportLoadedRectors($outputFormat);
        return \RectorPrefix20211020\Symfony\Component\Console\Command\Command::SUCCESS;
    }
    private function reportLoadedRectors(string $outputFormat) : void
    {
        $rectors = \array_filter($this->rectors, function (\Rector\Core\Contract\Rector\RectorInterface $rector) : bool {
            if ($rector instanceof \Rector\PostRector\Contract\Rector\PostRectorInterface) {
                return \false;
            }
            return !$rector instanceof \Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
        });
        $rectorCount = \count($rectors);
        if ($rectorCount === 0) {
            $warningMessage = \sprintf('No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.', \PHP_EOL . \PHP_EOL, \PHP_EOL);
            $this->outputStyle->warning($warningMessage);
            return;
        }
        $outputFormatter = $this->showOutputFormatterCollector->getByName($outputFormat);
        $outputFormatter->list($rectors);
    }
}
