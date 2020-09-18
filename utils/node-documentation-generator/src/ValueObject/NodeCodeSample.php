<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\ValueObject;

final class NodeCodeSample
{
    /**
     * @var string
     */
    private $phpCode;

    /**
     * @var string
     */
    private $printedContent;

    public function __construct(string $phpCode, string $printedContent)
    {
        $this->phpCode = $phpCode;
        $this->printedContent = $printedContent;
    }

    public function getPhpCode(): string
    {
        return $this->phpCode;
    }

    public function getPrintedContent(): string
    {
        return $this->printedContent;
    }
}
