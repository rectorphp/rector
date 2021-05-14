<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

final class RenamePackage
{
    public function __construct(
        private string $oldPackageName,
        private string $newPackageName
    ) {
    }

    public function getOldPackageName(): string
    {
        return $this->oldPackageName;
    }

    public function getNewPackageName(): string
    {
        return $this->newPackageName;
    }
}
