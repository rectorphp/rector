<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ShowCommand extends Command
{
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(
        private readonly RectorOutputStyle $rectorOutputStyle,
        private readonly array $rectors
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors with their configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->rectorOutputStyle->title('Loaded Rector rules');

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
            $this->rectorOutputStyle->warning($warningMessage);

            return self::FAILURE;
        }

        $rectorCount = count($rectors);
        foreach ($rectors as $rector) {
            $this->rectorOutputStyle->writeln(' * ' . $rector::class);
        }

        $message = sprintf('%d loaded Rectors', $rectorCount);
        $this->rectorOutputStyle->success($message);

        $this->rectorOutputStyle->error(
            'The "show" command is deprecated and will be removed, as it was used only for more output on Rector run. Use the "--debug" option and process command for debugging output instead.'
        );
        // to spot the error message
        sleep(3);

        return Command::FAILURE;
    }
}
