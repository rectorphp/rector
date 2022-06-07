<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
/**
 * @implements ConflictingNameGuardInterface<PropertyRename>
 */
final class NotPrivatePropertyGuard implements ConflictingNameGuardInterface
{
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool
    {
        return !$renameValueObject->isPrivateProperty();
    }
}
