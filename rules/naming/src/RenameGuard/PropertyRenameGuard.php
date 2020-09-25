<?php

declare(strict_types=1);

namespace Rector\Naming\RenameGuard;

use Rector\Naming\Guard\GuardInterface;
use Rector\Naming\ValueObject\RenameValueObjectInterface;

class PropertyRenameGuard implements RenameGuardInterface
{
    /**
     * @param GuardInterface[] $guards
     */
    public function shouldSkip(RenameValueObjectInterface $renameValueObject, array $guards): bool
    {
        foreach ($guards as $guard) {
            if ($guard->check($renameValueObject) === true) {
                return true;
            }
        }

        return false;
    }
}
