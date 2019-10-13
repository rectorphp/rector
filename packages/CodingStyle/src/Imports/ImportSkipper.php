<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class ImportSkipper
{
    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var AliasUsesResolver
     */
    private $aliasUsesResolver;

    /**
     * @var ShortNameResolver
     */
    private $shortNameResolver;

    public function __construct(
        UseAddingCommander $useAddingCommander,
        AliasUsesResolver $aliasUsesResolver,
        ShortNameResolver $shortNameResolver
    ) {
        $this->useAddingCommander = $useAddingCommander;
        $this->aliasUsesResolver = $aliasUsesResolver;
        $this->shortNameResolver = $shortNameResolver;
    }

    public function shouldSkipName(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        if ($this->isShortNameAlreadyUsedForDifferentFullyQualifiedName($node, $fullyQualifiedObjectType)) {
            return true;
        }

        if ($this->isShortNameAlreadyUsedInImportAlias($node, $fullyQualifiedObjectType)) {
            return true;
        }

        return ! $this->useAddingCommander->canImportBeAdded($node, $fullyQualifiedObjectType);
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
}
