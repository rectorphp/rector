<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ShowCommand extends Command
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private readonly OutputStyleInterface $outputStyle,
        private readonly array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputStyle->title('Loaded Rector rules');

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
            return self::SUCCESS;
        }

        $rectorCount = count($rectors);
        foreach ($rectors as $rector) {
            $this->outputStyle->writeln(' * ' . $rector::class);
        }

        $message = sprintf('%d loaded Rectors', $rectorCount);
        $this->outputStyle->success($message);

        return Command::SUCCESS;
    }
}
