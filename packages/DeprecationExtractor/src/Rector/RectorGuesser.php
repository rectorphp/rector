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

    public function guessForDeprecation(Deprecation $deprecation): ?RectorGuess
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

        return $this->rectorGuessFactory->createRemoval($message, $deprecation->getNode());
    }

    /**
     * @param Deprecation[] $deprecations
     * @return RectorGuess[]
     */
    public function guessForDeprecations(array $deprecations): array
    {
        $guessedRectors = [];

        foreach ($deprecations as $deprecation) {
            $guessedRectors[] = $this->guessForDeprecation($deprecation);
        }

        return $guessedRectors;
    }
}
