<?php

declare (strict_types=1);
namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;
final class MatchTypePropertyRenamer
{
    /**
     * @var \Rector\Naming\PropertyRenamer\PropertyRenamer
     */
    private $propertyRenamer;
    /**
     * @var \Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard
     */
    private $matchPropertyTypeConflictingNameGuard;
    public function __construct(\Rector\Naming\PropertyRenamer\PropertyRenamer $propertyRenamer, \Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard)
    {
        $this->propertyRenamer = $propertyRenamer;
        $this->matchPropertyTypeConflictingNameGuard = $matchPropertyTypeConflictingNameGuard;
    }
    public function rename(\Rector\Naming\ValueObject\PropertyRename $propertyRename) : ?\PhpParser\Node\Stmt\Property
    {
        if ($this->matchPropertyTypeConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }
        return $this->propertyRenamer->rename($propertyRename);
    }
}
