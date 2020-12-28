<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Contract\ClassNameImport;

use PhpParser\Node;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool;
}
