<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    public function __construct(
        private CurrentFileProvider $currentFileProvider,
        private UsedImportsResolver $usedImportsResolver,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return FullyQualified::class;
    }

    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $param = $this->betterNodeFinder->findParentType($node, Param::class);
        if ($param instanceof Param) {
            $possibleAliasedObjectType = null;

            if ($param->type instanceof NullableType && $param->type->type === $node) {
                $possibleAliasedObjectType = $this->resolvePossibleAliasedObjectType($node);
            }

            if ($param->type === $node) {
                $possibleAliasedObjectType = $this->resolvePossibleAliasedObjectType($node);
            }

            if ($possibleAliasedObjectType instanceof AliasedObjectType) {
                return $possibleAliasedObjectType;
            }
        }

        $originalName = (string) $node->getAttribute(AttributeKey::ORIGINAL_NAME);
        $fullyQualifiedName = $node->toString();

        // is aliased?
        if ($this->isAliasedName($originalName, $fullyQualifiedName) && $originalName !== $fullyQualifiedName
        ) {
            return new AliasedObjectType($originalName, $fullyQualifiedName);
        }

        return new FullyQualifiedObjectType($fullyQualifiedName);
    }

    private function resolvePossibleAliasedObjectType(FullyQualified $fullyQualified): ?AliasedObjectType
    {
        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            return null;
        }

        $oldTokens = $file->getOldTokens();
        $startTokenPos = $fullyQualified->getStartTokenPos();

        if (! isset($oldTokens[$startTokenPos][1])) {
            return null;
        }

        $type = $oldTokens[$startTokenPos][1];
        if (str_contains($type, '\\')) {
            return null;
        }

        $objectTypes = $this->usedImportsResolver->resolveForNode($fullyQualified);
        foreach ($objectTypes as $objectType) {
            if (! $objectType instanceof AliasedObjectType) {
                continue;
            }

            if ($objectType->getClassName() !== $type) {
                continue;
            }

            return $objectType;
        }

        return null;
    }

    private function isAliasedName(string $originalName, string $fullyQualifiedName): bool
    {
        if ($originalName === '') {
            return false;
        }

        if ($originalName === $fullyQualifiedName) {
            return false;
        }

        return ! \str_ends_with($fullyQualifiedName, '\\' . $originalName);
    }
}
