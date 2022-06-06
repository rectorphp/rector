<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Contract\ClassNameImport;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(\Rector\Core\ValueObject\Application\File $file, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType, \PhpParser\Node $node) : bool;
}
