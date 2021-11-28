<?php

declare (strict_types=1);
namespace Rector\Naming\Contract\Guard;

use Rector\Naming\Contract\RenameValueObjectInterface;
/**
 * @template TRename as RenameValueObjectInterface
 */
interface ConflictingNameGuardInterface
{
    /**
     * @param \Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject
     */
    public function isConflicting($renameValueObject) : bool;
}
