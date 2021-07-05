<?php

declare (strict_types=1);
namespace Rector\Naming\Contract\Guard;

use Rector\Naming\Contract\RenameValueObjectInterface;
interface ConflictingNameGuardInterface
{
    /**
     * @param \Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject
     */
    public function isConflicting($renameValueObject) : bool;
}
