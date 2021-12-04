<?php

declare(strict_types=1);

namespace Rector\Restoration\ValueObject;

final class CompleteImportForPartialAnnotation
{
    public function __construct(
        private readonly string $use,
        private readonly string $alias
    ) {
    }

    public function getUse(): string
    {
        return $this->use;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
