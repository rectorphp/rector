<?php declare(strict_types=1);

namespace Rector\RectorDefinition;

final class RectorDefinition
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var array|CodeSample[]
     */
    private $codeSamples = [];

    /**
     * @todo require at least 1 item?
     * @param CodeSample[] $codeSamples
     */
    public function __construct(string $description, array $codeSamples)
    {
        // array type check
        array_walk($codeSamples, function (CodeSample $codeSample): void {
        });

        $this->description = $description;
        $this->codeSamples = $codeSamples;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return CodeSample[]
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }
}
