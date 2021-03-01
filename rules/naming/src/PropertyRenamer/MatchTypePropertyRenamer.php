<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class MatchTypePropertyRenamer
{
    /**
     * @var MatchPropertyTypeConflictingNameGuard
     */
    private $matchPropertyTypeConflictingNameGuard;

    /**
     * @var PropertyRenamer
     */
    private $propertyRenamer;

    public function __construct(
        PropertyRenamer $propertyRenamer,
        MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard
    ) {
        $this->matchPropertyTypeConflictingNameGuard = $matchPropertyTypeConflictingNameGuard;
        $this->propertyRenamer = $propertyRenamer;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->matchPropertyTypeConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return $this->propertyRenamer->rename($propertyRename);
    }
}
