<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

final class RenamedNamespace
{
    public function __construct(
        private string $currentName,
        private string $oldNamespace,
        private string $newNamespace
    ) {
    }

    public function getNameInNewNamespace(): string
    {
        return str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }
}
