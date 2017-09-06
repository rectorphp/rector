<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\Console\Command;

use Rector\TriggerExtractor\TriggerExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExtractCommand extends Command
{
    /**
     * @var string
     */
    private const NAME = 'extract-deprecations';

    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    /**
     * @var TriggerExtractor
     */
    private $triggerExtractor;

    public function __construct(TriggerExtractor $triggerExtractor)
    {
        $this->triggerExtractor = $triggerExtractor;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Extract deprecation notes from PHP files in directory(ies).');
        $this->addArgument(
            self::ARGUMENT_SOURCE_NAME,
            InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            'One or more directory to be checked.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getArgument(self::ARGUMENT_SOURCE_NAME);
        $this->triggerExtractor->scanDirectories($source);

        // $output->writeln()
        // write found deprecations...

        return 0;
    }
}
