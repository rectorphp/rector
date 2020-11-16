<?php

declare(strict_types=1);

namespace Rector\Core\RectorDefinition;

use Symplify\RuleDocGenerator\Contract\CodeSampleInterface;

final class ComposerJsonAwareCodeSample implements CodeSampleInterface
{
    /**
     * @var string
     */
    private $badCode;

    /**
     * @var string
     */
    private $goodCode;

    /**
     * @var string
     */
    private $composerJsonContent;

    /**
     * @var string|null
     */
    private $extraFileContent;

    public function __construct(
        string $badCode,
        string $goodCode,
        string $composerJsonContent,
        ?string $extraFileContent = null
    ) {
        $this->badCode = $badCode;
        $this->goodCode = $goodCode;
        $this->composerJsonContent = $composerJsonContent;
        $this->extraFileContent = $extraFileContent;
    }

    public function getBadCode(): string
    {
        return $this->badCode;
    }

    public function getGoodCode(): string
    {
        return $this->goodCode;
    }

    public function getComposerJsonContent(): string
    {
        return $this->composerJsonContent;
    }

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }
}
