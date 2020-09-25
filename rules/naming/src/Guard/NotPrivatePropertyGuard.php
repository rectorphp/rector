<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObject\RenameValueObjectInterface;

class NotPrivatePropertyGuard implements GuardInterface
{
    /**
     * @param PropertyRename $renameValueObject
     */
    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return ! $renameValueObject->getNode()->isPrivate();
    }
}
