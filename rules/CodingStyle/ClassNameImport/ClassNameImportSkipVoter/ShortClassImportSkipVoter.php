<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
final class ShortClassImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        $className = ltrim($fullyQualifiedObjectType->getClassName(), '\\');
        if (substr_count($className, '\\') === 0) {
            return !SimpleParameterProvider::provideBoolParameter(Option::IMPORT_SHORT_CLASSES);
        }
        return \false;
    }
}
