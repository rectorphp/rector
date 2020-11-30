<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\Contract\Guard\GuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;

final class NotPrivatePropertyGuard implements GuardInterface
{
    /**
     * @param PropertyRename $renameValueObject
     */
    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return ! $renameValueObject->isPrivateProperty();
    }
}
