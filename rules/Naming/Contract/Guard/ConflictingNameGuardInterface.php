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
     * @param TRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool;
}
