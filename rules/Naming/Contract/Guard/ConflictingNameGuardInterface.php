<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Contract\Guard;

use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
/**
 * @template TRename as RenameValueObjectInterface
 */
interface ConflictingNameGuardInterface
{
    /**
     * @param TRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool;
}
