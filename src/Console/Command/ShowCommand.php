<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;

final class ShowCommand extends Command
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private SymfonyStyle $symfonyStyle,
        private array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportLoadedRectors();

        return ShellCode::SUCCESS;
    }

    private function reportLoadedRectors(): void
    {
        $rectors = array_filter($this->rectors, function (RectorInterface $rector) {
            return ! $rector instanceof PostRectorInterface;
        });

        $rectorCount = count($rectors);

        if ($rectorCount > 0) {
            $this->symfonyStyle->title('Loaded Rector rules');

            foreach ($rectors as $rector) {
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
}
