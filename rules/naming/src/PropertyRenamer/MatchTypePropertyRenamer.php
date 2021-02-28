<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\MatchPropertyTypeConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class MatchTypePropertyRenamer extends AbstractPropertyRenamer
{
    /**
     * @var MatchPropertyTypeConflictingNameGuard
     */
    private $matchPropertyTypeConflictingNameGuard;

    public function __construct(MatchPropertyTypeConflictingNameGuard $matchPropertyTypeConflictingNameGuard)
    {
        $this->matchPropertyTypeConflictingNameGuard = $matchPropertyTypeConflictingNameGuard;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->matchPropertyTypeConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return parent::rename($propertyRename);
    }
}
