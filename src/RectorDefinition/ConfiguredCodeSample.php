<?php

declare(strict_types=1);

namespace Rector\Core\RectorDefinition;

use Rector\Core\Contract\RectorDefinition\CodeSampleInterface;

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
     * @var string|null
     */
    private $extraFileContent;

    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        string $codeBefore,
        string $codeAfter,
        array $configuration,
        ?string $extraFileContent = null
    ) {
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->configuration = $configuration;
        $this->extraFileContent = $extraFileContent;
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

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }
}
