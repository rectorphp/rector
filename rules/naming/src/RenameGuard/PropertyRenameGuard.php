<?php

declare(strict_types=1);

namespace Rector\Naming\RenameGuard;

use Rector\Naming\Guard\GuardInterface;
use Rector\Naming\ValueObject\RenameValueObjectInterface;

final class PropertyRenameGuard implements RenameGuardInterface
{
    /**
     * @param GuardInterface[] $guards
     */
    public function shouldSkip(RenameValueObjectInterface $renameValueObject, array $guards): bool
    {
        foreach ($guards as $guard) {
            if ($guard->check($renameValueObject)) {
                return true;
            }
        }

        return false;
    }
}
