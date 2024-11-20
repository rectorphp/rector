<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser;

class ParserConfig
{
    public bool $useLinesAttributes;
    public bool $useIndexAttributes;
    /**
     * @param array{lines?: bool, indexes?: bool} $usedAttributes
     */
    public function __construct(array $usedAttributes)
    {
        $this->useLinesAttributes = $usedAttributes['lines'] ?? \false;
        $this->useIndexAttributes = $usedAttributes['indexes'] ?? \false;
    }
}
