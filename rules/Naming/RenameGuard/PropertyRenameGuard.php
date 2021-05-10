<?php

declare(strict_types=1);

namespace Rector\Naming\RenameGuard;

use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;

final class PropertyRenameGuard
{
    /**
     * @param ConflictingNameGuardInterface[] $conflictingNameGuards
     */
    public function __construct(
        private array $conflictingNameGuards
    ) {
    }

    public function shouldSkip(RenameValueObjectInterface $renameValueObject): bool
    {
        foreach ($this->conflictingNameGuards as $conflictingNameGuard) {
            if ($conflictingNameGuard->isConflicting($renameValueObject)) {
                return true;
            }
        }

        return false;
    }
}
