<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix20210514\Symfony\Component\Console\Command\Command;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode;
final class ShowCommand extends \RectorPrefix20210514\Symfony\Component\Console\Command\Command
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var mixed[]
     */
    private $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(\RectorPrefix20210514\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, array $rectors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Show loaded Rectors with their configuration');
    }
    protected function execute(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $this->reportLoadedRectors();
        return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
    }
    private function reportLoadedRectors() : void
    {
        $rectors = \array_filter($this->rectors, function (\Rector\Core\Contract\Rector\RectorInterface $rector) {
            return !$rector instanceof \Rector\PostRector\Contract\Rector\PostRectorInterface;
        });
        $rectorCount = \count($rectors);
        if ($rectorCount > 0) {
            $this->symfonyStyle->title('Loaded Rector rules');
            foreach ($rectors as $rector) {
                $this->symfonyStyle->writeln(' * ' . \get_class($rector));
            }
            $message = \sprintf('%d loaded Rectors', $rectorCount);
            $this->symfonyStyle->success($message);
        } else {
            $warningMessage = \sprintf('No Rectors were loaded.%sAre sure your "rector.php" config is in the root?%sTry "--config <path>" option to include it.', \PHP_EOL . \PHP_EOL, \PHP_EOL);
            $this->symfonyStyle->warning($warningMessage);
        }
    }
}
