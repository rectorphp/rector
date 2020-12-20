<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\Contract\Guard\ConflictingGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;

final class NotPrivatePropertyGuard implements ConflictingGuardInterface
{
    /**
     * @param PropertyRename $renameValueObject
     */
    public function check(RenameValueObjectInterface $renameValueObject): bool
    {
        return ! $renameValueObject->isPrivateProperty();
    }
}
