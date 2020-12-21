<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\Contract\Guard\ConflictingGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;

final class HasMagicGetSetGuard implements ConflictingGuardInterface
{
    /**
     * @param PropertyRename $renameValueObject
     */
    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return method_exists($renameValueObject->getClassLikeName(), '__set') || method_exists(
            $renameValueObject->getClassLikeName(),
            '__get'
        );
    }
}
