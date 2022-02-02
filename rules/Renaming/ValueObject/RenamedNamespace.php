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
        if ($this->newNamespace === $this->currentName) {
            return $this->currentName;
        }

        return str_replace($this->oldNamespace, $this->newNamespace, $this->currentName);
    }

    public function getNewNamespace(): string
    {
        return $this->newNamespace;
    }
}
