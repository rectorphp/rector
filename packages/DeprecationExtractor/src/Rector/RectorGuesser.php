<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Rector;

/**
 * This class tries to guess, which Rector could be used to create refactoring
 * based on deprecation message and related options.
 */
final class RectorGuesser
{
    /**
     * @var AnnotationRectorGuesser
     */
    private $annotationRectorGuesser;

    /**
     * @var TriggerErrorRectorGuesser
     */
    private $triggerErrorRectorGuesser;

    public function __construct(
        AnnotationRectorGuesser $annotationRectorGuesser,
        TriggerErrorRectorGuesser $triggerErrorRectorGuesser
    ) {
        $this->annotationRectorGuesser = $annotationRectorGuesser;
        $this->triggerErrorRectorGuesser = $triggerErrorRectorGuesser;
    }

    /**
     * @param mixed[] $annotations
     * @return RectorGuess[]
     */
    public function guessForAnnotations(array $annotations): array
    {
        $guessedRectors = [];

        foreach ($annotations as $annotation) {
            $guessedRectors[] = $this->annotationRectorGuesser->guess(
                $annotation['message'],
                $annotation['node']
            );
        }

        return $guessedRectors;
    }

    /**
     * @param mixed[] $triggerErrors
     * @return RectorGuess[]
     */
    public function guessForTriggerErrors(array $triggerErrors): array
    {
        $guessedRectors = [];

        foreach ($triggerErrors as $triggerError) {
            $guessedRectors[] = $this->triggerErrorRectorGuesser->guess($triggerError);
        }

        return $guessedRectors;
    }
}
