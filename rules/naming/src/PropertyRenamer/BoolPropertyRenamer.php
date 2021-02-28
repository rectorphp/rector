<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\BoolPropertyConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class BoolPropertyRenamer extends AbstractPropertyRenamer
{
    /**
     * @var BoolPropertyConflictingNameGuard
     */
    private $boolPropertyConflictingNameGuard;

    public function __construct(BoolPropertyConflictingNameGuard $boolPropertyConflictingNameGuard)
    {
        $this->boolPropertyConflictingNameGuard = $boolPropertyConflictingNameGuard;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->boolPropertyConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return parent::rename($propertyRename);
    }
}
