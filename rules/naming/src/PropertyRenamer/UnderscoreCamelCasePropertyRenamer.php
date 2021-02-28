<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node\Stmt\Property;
use Rector\Naming\Guard\PropertyConflictingNameGuard\UnderscoreCamelCaseConflictingNameGuard;
use Rector\Naming\ValueObject\PropertyRename;

final class UnderscoreCamelCasePropertyRenamer extends AbstractPropertyRenamer
{
    /**
     * @var UnderscoreCamelCaseConflictingNameGuard
     */
    private $underscoreCamelCaseConflictingNameGuard;

    public function __construct(UnderscoreCamelCaseConflictingNameGuard $underscoreCamelCaseConflictingNameGuard)
    {
        $this->underscoreCamelCaseConflictingNameGuard = $underscoreCamelCaseConflictingNameGuard;
    }

    public function rename(PropertyRename $propertyRename): ?Property
    {
        if ($this->underscoreCamelCaseConflictingNameGuard->isConflicting($propertyRename)) {
            return null;
        }

        return parent::rename($propertyRename);
    }
}
