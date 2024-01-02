<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Contract\ClassNameImport;

use PhpParser\Node;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool;
}
