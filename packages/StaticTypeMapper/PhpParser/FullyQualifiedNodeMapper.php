<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\PhpParser;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use RectorPrefix20220606\Rector\Core\Provider\CurrentFileProvider;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements PhpParserNodeMapperInterface<FullyQualified>
 */
final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\UsedImportsResolver
     */
    private $usedImportsResolver;
    public function __construct(CurrentFileProvider $currentFileProvider, UsedImportsResolver $usedImportsResolver)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->usedImportsResolver = $usedImportsResolver;
    }
    public function getNodeType() : string
    {
        return FullyQualified::class;
    }
    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isParamTyped($node, $parent)) {
            $possibleAliasedObjectType = $this->resolvePossibleAliasedObjectType($node);
            if ($possibleAliasedObjectType instanceof AliasedObjectType) {
                return $possibleAliasedObjectType;
            }
        }
        $originalName = (string) $node->getAttribute(AttributeKey::ORIGINAL_NAME);
        $fullyQualifiedName = $node->toString();
        // is aliased?
        if ($this->isAliasedName($originalName, $fullyQualifiedName) && $originalName !== $fullyQualifiedName) {
            return new AliasedObjectType($originalName, $fullyQualifiedName);
        }
        return new FullyQualifiedObjectType($fullyQualifiedName);
    }
    private function isParamTyped(FullyQualified $fullyQualified, ?Node $node) : bool
    {
        if ($node instanceof Param && $node->type === $fullyQualified) {
            return \true;
        }
        if (!$node instanceof NullableType) {
            return \false;
        }
        $parentOfParent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentOfParent instanceof Param) {
            return \false;
        }
        return $node->type === $fullyQualified;
    }
    private function resolvePossibleAliasedObjectType(FullyQualified $fullyQualified) : ?AliasedObjectType
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $fullyQualified->getStartTokenPos();
        if (!isset($oldTokens[$startTokenPos][1])) {
            return null;
        }
        $type = $oldTokens[$startTokenPos][1];
        if (\strpos((string) $type, '\\') !== \false) {
            return null;
        }
        $objectTypes = $this->usedImportsResolver->resolveForNode($fullyQualified);
        foreach ($objectTypes as $objectType) {
            if (!$objectType instanceof AliasedObjectType) {
                continue;
            }
            if ($objectType->getClassName() !== $type) {
                continue;
            }
            return $objectType;
        }
        return null;
    }
    private function isAliasedName(string $originalName, string $fullyQualifiedName) : bool
    {
        if ($originalName === '') {
            return \false;
        }
        if ($originalName === $fullyQualifiedName) {
            return \false;
        }
        return \substr_compare($fullyQualifiedName, '\\' . $originalName, -\strlen('\\' . $originalName)) !== 0;
    }
}
