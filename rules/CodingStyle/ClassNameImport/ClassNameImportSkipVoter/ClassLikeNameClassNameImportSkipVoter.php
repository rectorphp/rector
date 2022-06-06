<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use RectorPrefix20220606\Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * Prevents adding:
 *
 * use App\SomeClass;
 *
 * If there is already:
 *
 * class SomeClass {}
 */
final class ClassLikeNameClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ShortNameResolver
     */
    private $shortNameResolver;
    public function __construct(ShortNameResolver $shortNameResolver)
    {
        $this->shortNameResolver = $shortNameResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool
    {
        $classLikeNames = $this->shortNameResolver->resolveShortClassLikeNamesForNode($node);
        if ($classLikeNames === []) {
            return \false;
        }
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        foreach ($classLikeNames as $classLikeName) {
            if (\strtolower($classLikeName) === $shortNameLowered) {
                return \true;
            }
        }
        return \false;
    }
}
