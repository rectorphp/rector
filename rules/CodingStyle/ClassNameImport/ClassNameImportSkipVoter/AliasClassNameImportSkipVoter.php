<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
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
 * use App\Something as SomeClass;
 */
final class AliasClassNameImportSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\AliasUsesResolver
     */
    private $aliasUsesResolver;
    public function __construct(AliasUsesResolver $aliasUsesResolver)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool
    {
        $aliasedUses = $this->aliasUsesResolver->resolveFromNode($node);
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        foreach ($aliasedUses as $aliasedUse) {
            $aliasedUseLowered = \strtolower($aliasedUse);
            // its aliased, we cannot just rename it
            if (\substr_compare($aliasedUseLowered, '\\' . $shortNameLowered, -\strlen('\\' . $shortNameLowered)) === 0) {
                return \true;
            }
        }
        return \false;
    }
}
