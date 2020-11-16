<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\RenameGuard;

use Rector\Naming\Contract\Guard\GuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;

interface RenameGuardInterface
{
    /**
     * @param GuardInterface[] $guards
     */
    public function shouldSkip(RenameValueObjectInterface $renameValueObject, array $guards): bool;
}
