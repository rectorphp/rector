<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Console\Command;

use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\DeprecationExtractor\Rector\RectorGuesser;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\Naming\CommandNaming;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This commands shows suggested rector to use to resolve deprecation.
 *
 * We need this manually to analyze other versions and do not actually create any rector.
 */
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

    /**
     * @var RectorGuesser
     */
    private $rectorGuesser;

    public function __construct(
        DeprecationExtractor $deprecationExtractor,
        DeprecationCollector $deprecationCollector,
        RectorGuesser $rectorGuesser,
        SymfonyStyle $symfonyStyle
    ) {
        $this->deprecationExtractor = $deprecationExtractor;
        $this->deprecationCollector = $deprecationCollector;
        $this->rectorGuesser = $rectorGuesser;
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
        $this->deprecationExtractor->scanDirectories(
            $input->getArgument(self::ARGUMENT_SOURCE_NAME)
        );

        $this->symfonyStyle->title(sprintf(
            'Found %d deprecations',
            count($this->deprecationCollector->getDeprecationAnnotations())
            + count($this->deprecationCollector->getDeprecationTriggerErrors())
        ));

        $guessedRectors = array_merge(
            $this->rectorGuesser->guessForAnnotations(
                $this->deprecationCollector->getDeprecationAnnotations()
            ),
            $this->rectorGuesser->guessForTriggerErrors(
                $this->deprecationCollector->getDeprecationTriggerErrors()
            )
        );

        /** @var RectorGuess $guessedRector */
        foreach ($guessedRectors as $guessedRector) {
            if ($this->shouldSkipGuessedRector($guessedRector)) {
                continue;
            }

            if ($guessedRector->canBeCreated()) {
                $this->symfonyStyle->success($guessedRector->getGuessedRectorClass());
            } else {
                $this->symfonyStyle->warning($guessedRector->getGuessedRectorClass());
            }

            $this->symfonyStyle->writeln(' - certainity: ' . $guessedRector->getCertainity());
            $this->symfonyStyle->writeln(' - node: ' . $guessedRector->getNodeClass());

            $this->symfonyStyle->newLine();

            $this->symfonyStyle->writeln($guessedRector->getMessage());
            // @todo: add metadata like related class, method etc. - or maybe get from NODE like
            // $node->getAttribute(Attributes::CLASS_NODE)
            // $node->getAttribute(Attributes::METHOD_NODE)

            $this->symfonyStyle->newLine(2);
        }

        return 0;
    }

    protected function shouldSkipGuessedRector(?RectorGuess $guessedRector): bool
    {
        if ($guessedRector === null) {
            return true;
        }

        if ($guessedRector->getGuessedRectorClass() === RectorGuess::YAML_CONFIGURATION) {
            return true;
        }

        return false;
    }
}
