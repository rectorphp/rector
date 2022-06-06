<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\RenameGuard;

use RectorPrefix20220606\Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
final class PropertyRenameGuard
{
    /**
     * @var ConflictingNameGuardInterface[]
     * @readonly
     */
    private $conflictingNameGuards;
    /**
     * @param ConflictingNameGuardInterface[] $conflictingNameGuards
     */
    public function __construct(array $conflictingNameGuards)
    {
        $this->conflictingNameGuards = $conflictingNameGuards;
    }
    public function shouldSkip(RenameValueObjectInterface $renameValueObject) : bool
    {
        foreach ($this->conflictingNameGuards as $conflictingNameGuard) {
            if ($conflictingNameGuard->isConflicting($renameValueObject)) {
                return \true;
            }
        }
        return \false;
    }
}
