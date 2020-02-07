<?php

declare(strict_types=1);

namespace Rector\Core\RectorDefinition;

use Rector\Core\Contract\RectorDefinition\CodeSampleInterface;

/**
 * @see \Rector\Core\Tests\RectorDefinition\RectorDefinitionTest
 */
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
    public function __construct(string $description, array $codeSamples = [])
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
     * @param CodeSampleInterface[] $codeSamples
     */
    private function ensureCodeSamplesAreValid(array $codeSamples): void
    {
        array_walk($codeSamples, function (CodeSampleInterface $codeSample): void {
            // array type check
        });
    }
}
