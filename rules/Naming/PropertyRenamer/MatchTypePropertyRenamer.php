<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\PropertyRenamer;

use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;
use RectorPrefix20220606\Rector\Naming\ValueObject\PropertyRename;
final class MatchTypePropertyRenamer
{
    /**
     * @readonly
     * @var \Rector\Naming\PropertyRenamer\PropertyRenamer
     */
    private $propertyRenamer;
    /**
     * @readonly
     * @var \Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard
     */
    private $matchPropertyTypeConflictingNameGuard;
    public function __construct(PropertyRenamer $propertyRenamer, MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard)
    {
        $this->propertyRenamer = $propertyRenamer;
        $this->matchPropertyTypeConflictingNameGuard = $matchPropertyTypeConflictingNameGuard;
    }
    public function rename(PropertyRename $propertyRename) : ?Property
    {
        if ($this->matchPropertyTypeConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }
        return $this->propertyRenamer->rename($propertyRename);
    }
}
