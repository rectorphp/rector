<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Console\Command;

use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\Naming\CommandNaming;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// @todo match the name...
final class ExtractDeprecationsCommand extends Command
{
    /**
     * @var string
     */
    private const ARGUMENT_SOURCE_NAME = 'source';

    /**
     * @var DeprecationExtractor
     */
    private $deprecationExtractor;

    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        DeprecationExtractor $deprecationExtractor,
        DeprecationCollector $deprecationCollector,
        SymfonyStyle $symfonyStyle
    ) {
        $this->deprecationExtractor = $deprecationExtractor;
        $this->deprecationCollector = $deprecationCollector;
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
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
        $this->deprecationExtractor->scanDirectories($source);

        $this->symfonyStyle->note(sprintf(
            'Found %d deprecations.',
            count($this->deprecationCollector->getDeprecations())
        ));

        foreach ($this->deprecationCollector->getDeprecations() as $deprecation) {
            $output->writeln($deprecation);
        }

        return 0;
    }
}
