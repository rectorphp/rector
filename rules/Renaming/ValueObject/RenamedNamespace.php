<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

final class RenamedNamespace
{
    public function __construct(
        private readonly string $currentName,
        private readonly string $oldNamespace,
        private readonly string $newNamespace
    ) {
    }

    public function getNameInNewNamespace(): string
    {
        return str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }
}
