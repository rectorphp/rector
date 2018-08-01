<?php declare(strict_types=1);

namespace Rector\RectorDefinition;

use Rector\Contract\RectorDefinition\CodeSampleInterface;

final class ConfiguredCodeSample implements CodeSampleInterface
{
    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var mixed[]
     */
    private $configuration = [];

    /**
     * @param mixed[] $configuration
     */
    public function __construct(string $codeBefore, string $codeAfter, array $configuration)
    {
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->configuration = $configuration;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    /**
     * @return mixed[]
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
