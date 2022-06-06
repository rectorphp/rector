<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Guard;

use RectorPrefix20220606\Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
use RectorPrefix20220606\Rector\Naming\ValueObject\PropertyRename;
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
