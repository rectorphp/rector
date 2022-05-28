<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\ValueObject;

use PhpParser\Node\Stmt\UseUse;

final class UseAliasMetadata
{
    public function __construct(
        private readonly string $shortAttributeName,
        private readonly string $useImportName,
        private readonly UseUse $useUse
    ) {
    }

    public function getShortAttributeName(): string
    {
        return $this->shortAttributeName;
    }

    public function getUseImportName(): string
    {
        return $this->useImportName;
    }

    public function getUseUse(): UseUse
    {
        return $this->useUse;
    }
}
