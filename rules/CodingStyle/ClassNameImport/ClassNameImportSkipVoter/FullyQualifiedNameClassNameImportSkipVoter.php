<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * Prevents adding:
 *
 * use App\SomeClass;
 *
 * If there is already:
 *
 * SomeClass::callThis();
 */
final class FullyQualifiedNameClassNameImportSkipVoter implements \Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface
{
    /**
     * @var \Rector\CodingStyle\ClassNameImport\ShortNameResolver
     */
    private $shortNameResolver;
    public function __construct(\Rector\CodingStyle\ClassNameImport\ShortNameResolver $shortNameResolver)
    {
        $this->shortNameResolver = $shortNameResolver;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType
     * @param \PhpParser\Node $node
     */
    public function shouldSkip($file, $fullyQualifiedObjectType, $node) : bool
    {
        // "new X" or "X::static()"
        $shortNamesToFullyQualifiedNames = $this->shortNameResolver->resolveForNode($file);
        foreach ($shortNamesToFullyQualifiedNames as $shortName => $fullyQualifiedName) {
            $shortNameLowered = \strtolower($shortName);
            if ($fullyQualifiedObjectType->getShortNameLowered() !== $shortNameLowered) {
                continue;
            }
            return $fullyQualifiedObjectType->getClassNameLowered() !== \strtolower($fullyQualifiedName);
        }
        return \false;
    }
}
