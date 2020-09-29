<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObject\RenameValueObjectInterface;

final class HasMagicGetSetGuard implements GuardInterface
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
