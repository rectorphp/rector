<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

use Nette\Utils\Strings;
use Rector\DeprecationExtractor\Deprecation\Deprecation;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;

/**
 * This class tries to guess, which Rector could be used to create refactoring
 * based on deprecation message and related options.
 */
final class RectorGuesser
{
    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    /**
     * @var UnsupportedDeprecationFilter
     */
    private $unsupportedDeprecationFilter;

    public function __construct(
        RectorGuessFactory $rectorGuessFactory,
        UnsupportedDeprecationFilter $unsupportedDeprecationFilter
    ) {
        $this->rectorGuessFactory = $rectorGuessFactory;
        $this->unsupportedDeprecationFilter = $unsupportedDeprecationFilter;
    }

    /**
     * @param Deprecation[] $deprecations
     * @return RectorGuess[]
     */
    public function guessForDeprecations(array $deprecations): array
    {
        $rectorGuesses = [];

        foreach ($deprecations as $deprecation) {
            $rectorGuesses[] = $this->guessForDeprecation($deprecation);
        }

        return $rectorGuesses;
    }

    private function guessForDeprecation(Deprecation $deprecation): ?RectorGuess
    {
        $message = $deprecation->getMessage();

        if ($this->unsupportedDeprecationFilter->matches($deprecation)) {
            return $this->rectorGuessFactory->createUnsupported($message, $deprecation->getNode());
        }

        if (Strings::contains($message, 'It will be made mandatory in') ||
            Strings::contains($message, 'Not defining it is deprecated since')
        ) {
            return $this->rectorGuessFactory->createNewArgument($message, $deprecation->getNode());
        }

        $possibleRectorGuess = $this->processUseInMessage($deprecation, $message);
        if ($possibleRectorGuess) {
            return $possibleRectorGuess;
        }

        if (Strings::contains($message, 'Replace') || Strings::contains($message, 'replace')) {
            return $this->rectorGuessFactory->createMethodNameReplacerGuess(
                $message,
                $deprecation->getNode()
            );
        }

        return $this->rectorGuessFactory->createRemoval($message, $deprecation->getNode());
    }

    private function processUseInMessage(Deprecation $deprecation, string $message): ?RectorGuess
    {
        $result = Strings::split($message, '#use |Use#');

        if (count($result) === 2) {
            if (Strings::contains($message, 'class is deprecated')) {
                return $this->rectorGuessFactory->createClassReplacer(
                    $message,
                    $deprecation->getNode()
                );
            }

            return $this->rectorGuessFactory->createMethodNameReplacerGuess(
                $message,
                $deprecation->getNode()
            );
        }

        return null;
    }
}
