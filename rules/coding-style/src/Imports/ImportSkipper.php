<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Validator\CanImportBeAddedValidator;

final class ImportSkipper
{
    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    /**
     * @var CanImportBeAddedValidator
     */
    private $canImportBeAddedValidator;

    public function __construct(
        AliasUsesResolver $aliasUsesResolver,
        CanImportBeAddedValidator $canImportBeAddedValidator,
        ShortNameResolver $shortNameResolver
    ) {
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->shortNameResolver = $shortNameResolver;
        $this->canImportBeAddedValidator = $canImportBeAddedValidator;
    }

    public function shouldSkipNameForFullyQualifiedObjectType(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        if ($this->isShortNameAlreadyUsedForDifferentFullyQualifiedName($node, $fullyQualifiedObjectType)) {
            return true;
        }

        if ($this->isShortNameAlreadyUsedInImportAlias($node, $fullyQualifiedObjectType)) {
            return true;
        }

        if ($this->isNameAlreadyUsedInClassLikeName($node, $fullyQualifiedObjectType)) {
            return true;
        }

        return ! $this->canImportBeAddedValidator->canImportBeAdded($node, $fullyQualifiedObjectType);
    }

    private function isShortNameAlreadyUsedForDifferentFullyQualifiedName(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        // "new X" or "X::static()"
        $shortNames = $this->shortNameResolver->resolveForNode($node);

        foreach ($shortNames as $shortName => $fullyQualifiedName) {
            if ($fullyQualifiedObjectType->getShortName() !== $shortName) {
                continue;
            }

            return $fullyQualifiedObjectType->getClassName() !== $fullyQualifiedName;
        }

        return false;
    }

    private function isShortNameAlreadyUsedInImportAlias(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        $aliasedUses = $this->aliasUsesResolver->resolveForNode($node);
        foreach ($aliasedUses as $aliasedUse) {
            // its aliased, we cannot just rename it
            if (Strings::endsWith($aliasedUse, '\\' . $fullyQualifiedObjectType->getShortName())) {
                return true;
            }
        }

        return false;
    }

    private function isNameAlreadyUsedInClassLikeName(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        $classLikeNames = $this->shortNameResolver->resolveShortClassLikeNamesForNode($node);

        return in_array($fullyQualifiedObjectType->getShortName(), $classLikeNames, true);
    }
}
