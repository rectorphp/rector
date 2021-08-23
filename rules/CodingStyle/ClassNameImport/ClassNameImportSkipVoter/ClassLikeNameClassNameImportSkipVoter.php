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
 * class SomeClass {}
 */
final class ClassLikeNameClassNameImportSkipVoter implements \Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface
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
        $classLikeNames = $this->shortNameResolver->resolveShortClassLikeNamesForNode($node);
        foreach ($classLikeNames as $classLikeName) {
            if (\strtolower($classLikeName) === $fullyQualifiedObjectType->getShortNameLowered()) {
                return \true;
            }
        }
        return \false;
    }
}
