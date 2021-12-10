<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\CodingStyle\ClassNameImport\UsedImportsResolver;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements PhpParserNodeMapperInterface<FullyQualified>
 */
final class FullyQualifiedNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
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
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\CodingStyle\ClassNameImport\UsedImportsResolver $usedImportsResolver)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->usedImportsResolver = $usedImportsResolver;
    }
    public function getNodeType() : string
    {
        return \PhpParser\Node\Name\FullyQualified::class;
    }
    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($this->isParamTyped($node, $parent)) {
            $possibleAliasedObjectType = $this->resolvePossibleAliasedObjectType($node);
            if ($possibleAliasedObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
                return $possibleAliasedObjectType;
            }
        }
        $originalName = (string) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
        $fullyQualifiedName = $node->toString();
        // is aliased?
        if ($this->isAliasedName($originalName, $fullyQualifiedName) && $originalName !== $fullyQualifiedName) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($originalName, $fullyQualifiedName);
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($fullyQualifiedName);
    }
    private function isParamTyped(\PhpParser\Node\Name\FullyQualified $fullyQualified, ?\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Param && $node->type === $fullyQualified) {
            return \true;
        }
        if (!$node instanceof \PhpParser\Node\NullableType) {
            return \false;
        }
        $parentOfParent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentOfParent instanceof \PhpParser\Node\Param) {
            return \false;
        }
        return $node->type === $fullyQualified;
    }
    private function resolvePossibleAliasedObjectType(\PhpParser\Node\Name\FullyQualified $fullyQualified) : ?\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof \Rector\Core\ValueObject\Application\File) {
            return null;
        }
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $fullyQualified->getStartTokenPos();
        if (!isset($oldTokens[$startTokenPos][1])) {
            return null;
        }
        $type = $oldTokens[$startTokenPos][1];
        if (\strpos($type, '\\') !== \false) {
            return null;
        }
        $objectTypes = $this->usedImportsResolver->resolveForNode($fullyQualified);
        foreach ($objectTypes as $objectType) {
            if (!$objectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
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
