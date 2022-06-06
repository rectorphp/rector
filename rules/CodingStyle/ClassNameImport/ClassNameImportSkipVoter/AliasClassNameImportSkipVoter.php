<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
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
 * use App\Something as SomeClass;
 */
final class AliasClassNameImportSkipVoter implements \Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\AliasUsesResolver
     */
    private $aliasUsesResolver;
    public function __construct(\Rector\CodingStyle\ClassNameImport\AliasUsesResolver $aliasUsesResolver)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
    }
    public function shouldSkip(\Rector\Core\ValueObject\Application\File $file, \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $fullyQualifiedObjectType, \PhpParser\Node $node) : bool
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
