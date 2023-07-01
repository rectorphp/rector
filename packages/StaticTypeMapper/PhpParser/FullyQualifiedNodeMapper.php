<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements PhpParserNodeMapperInterface<FullyQualified>
 */
final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType() : string
    {
        return FullyQualified::class;
    }
    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $originalName = (string) $node->getAttribute(AttributeKey::ORIGINAL_NAME);
        $fullyQualifiedName = $node->toString();
        // is aliased?
        if ($this->isAliasedName($originalName, $fullyQualifiedName) && $originalName !== $fullyQualifiedName) {
            return new AliasedObjectType($originalName, $fullyQualifiedName);
        }
        return new FullyQualifiedObjectType($fullyQualifiedName);
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
