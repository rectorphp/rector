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
