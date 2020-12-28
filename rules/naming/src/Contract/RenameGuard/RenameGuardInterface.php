<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\RenameGuard;

use Rector\Naming\Contract\Guard\ConflictingGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;

interface RenameGuardInterface
{
    /**
     * @param ConflictingGuardInterface[] $guards
     */
    public function shouldSkip(RenameValueObjectInterface $renameValueObject, array $guards): bool;
}
