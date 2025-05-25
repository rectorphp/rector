<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ClassNameImportSkipVoter;

use PhpParser\Node;
use Rector\CodingStyle\ClassNameImport\AliasUsesResolver;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;
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
     */
    private AliasUsesResolver $aliasUsesResolver;
    public function __construct(AliasUsesResolver $aliasUsesResolver)
    {
        $this->aliasUsesResolver = $aliasUsesResolver;
    }
    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node) : bool
    {
        $aliasedUses = $this->aliasUsesResolver->resolveFromNode($node, $file->getNewStmts());
        $longNameLowered = \strtolower($fullyQualifiedObjectType->getClassName());
        $shortNameLowered = $fullyQualifiedObjectType->getShortNameLowered();
        foreach ($aliasedUses as $aliasedUse) {
            $aliasedUseLowered = \strtolower($aliasedUse);
            // its aliased, we cannot just rename it
            if (\substr_compare($aliasedUseLowered, '\\' . $shortNameLowered, -\strlen('\\' . $shortNameLowered)) === 0) {
                return \true;
            }
            if ($aliasedUseLowered === $shortNameLowered && $longNameLowered === $shortNameLowered) {
                return \true;
            }
        }
        return \false;
    }
}
