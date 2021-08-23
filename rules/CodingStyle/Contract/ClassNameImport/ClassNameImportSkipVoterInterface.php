<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Contract\ClassNameImport;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
interface ClassNameImportSkipVoterInterface
{
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     * @param \PhpParser\Node $node
     */
    public function shouldSkip($file, $fullyQualifiedObjectType, $node) : bool;
}
