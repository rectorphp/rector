<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Rector\Console\Shell;
use Rector\Contract\Rector\RectorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ShowCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, array $rectors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectors = $rectors;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Show loaded Rectors');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rectorClasses = array_map(function (RectorInterface $rector) {
            return get_class($rector);
        }, $this->rectors);

        sort($rectorClasses);

        $this->symfonyStyle->listing($rectorClasses);
        $this->symfonyStyle->success(sprintf('%d loaded Rectors', count($rectorClasses)));

        return Shell::CODE_SUCCESS;
    }
}
