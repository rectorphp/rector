<?php declare(strict_types=1);

namespace Rector\RectorDefinition;

use Rector\Contract\RectorDefinition\CodeSampleInterface;
use Rector\Exception\RectorDefinition\CodeSamplesMissingException;
use function Safe\sprintf;

final class RectorDefinition
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var CodeSampleInterface[]
     */
    private $codeSamples = [];

    /**
     * @param CodeSampleInterface[] $codeSamples
     */
    public function __construct(string $description, array $codeSamples)
    {
        $this->ensureCodeSamplesAreValid($codeSamples);

        $this->description = $description;
        $this->codeSamples = $codeSamples;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return CodeSampleInterface[]
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }

    /**
     * At least 1 sample is required, so both author and reader have the same knowledge.
     *
     * @param CodeSampleInterface[] $codeSamples
     */
    private function ensureCodeSamplesAreValid(array $codeSamples): void
    {
        // array type check
        array_walk($codeSamples, function (CodeSampleInterface $codeSample): void {
        });

        if (count($codeSamples)) {
            return;
        }

        throw new CodeSamplesMissingException(sprintf(
            'At least 1 code sample is required for the "%s" class 2nd argument, so docs and examples can be generated. %d given',
            self::class,
            count($codeSamples)
        ));
    }
}
