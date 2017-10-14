<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Console\Command;

use Rector\DeprecationExtractor\Deprecation\DeprecationCollector;
use Rector\DeprecationExtractor\DeprecationExtractor;
use Rector\DeprecationExtractor\Rector\RectorGuesser;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\Naming\CommandNaming;
use Rector\Node\Attribute;
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

        $guessedRectors = $this->rectorGuesser->guessForDeprecations($this->deprecationCollector->getDeprecations());
        $guessedRectors = $this->filterOutUsefulGuessedRectors($guessedRectors);
        $guessedRectors = $this->filterOutDuplicatedGuessedRectors($guessedRectors);

        $this->symfonyStyle->title(sprintf(
            'Found %d useful deprecations',
            count($guessedRectors)
        ));

        foreach ($guessedRectors as $guessedRector) {
            // filter out duplicated deprecations, trigger_error + @deprecated annotation

            // $this->renderGuessedRector($guessedRector);
        }

        return 0;
    }

    /**
     * @param RectorGuess[] $guessedRectors
     * @return RectorGuess[]
     */
    private function filterOutUsefulGuessedRectors(array $guessedRectors): array
    {
        return array_filter($guessedRectors, function (RectorGuess $rectorGuess) {
            return $rectorGuess->isUseful();
        });
    }

    /**
     * @param RectorGuess[] $guessedRectors
     * @return RectorGuess[]
     */
    private function filterOutDuplicatedGuessedRectors(array $guessedRectors): array
    {
        $allMessages = [];
        foreach ($guessedRectors as $rectorGuess) {
            $allMessages[] = $rectorGuess->getMessage();
        }

        $filteredGuessedRectors = [];
        foreach ($guessedRectors as $rectorGuess) {
            foreach ($allMessages as $message) {
                // experimental; maybe count from message length?
                $levenshtein = levenshtein($rectorGuess->getMessage(), $message);
                if ($levenshtein !== 0 && $levenshtein < 10) {
                    continue 2;
                }
            }

            $filteredGuessedRectors[] = $rectorGuess;
        }

        return $filteredGuessedRectors;
    }

    private function renderGuessedRector(RectorGuess $guessedRector): void
    {
        $this->symfonyStyle->success($guessedRector->getGuessedRectorClass());

        $this->symfonyStyle->writeln(' ' . $guessedRector->getMessage());
        $this->symfonyStyle->newLine();

        $node = $guessedRector->getNode();

        $this->symfonyStyle->writeln(' Namespace: ' . $node->getAttribute(Attribute::NAMESPACE));
        $this->symfonyStyle->writeln(' Class: ' . $node->getAttribute(Attribute::CLASS_NAME));
        $this->symfonyStyle->writeln(' Scope: ' . $node->getAttribute(Attribute::SCOPE));
        $this->symfonyStyle->writeln(' Related node: ' . get_class($node));

        $this->symfonyStyle->newLine(2);
    }
}
