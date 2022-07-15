<?php

declare (strict_types=1);
namespace Rector\Naming\RenameGuard;

use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\ValueObject\PropertyRename;
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
    public function shouldSkip(PropertyRename $propertyRename) : bool
    {
        foreach ($this->conflictingNameGuards as $conflictingNameGuard) {
            if ($conflictingNameGuard->isConflicting($propertyRename)) {
                return \true;
            }
        }
        return \false;
    }
}
