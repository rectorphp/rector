<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\Contract\ClassNameImport;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
interface ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool;
}
