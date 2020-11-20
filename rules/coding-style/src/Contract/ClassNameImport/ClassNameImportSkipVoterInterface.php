<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Contract\ClassNameImport;

use PhpParser\Node;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool;
}
