<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Mapper;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
final class PhpParserNodeMapper
{
    /**
     * @var PhpParserNodeMapperInterface[]
     * @readonly
     */
    private $phpParserNodeMappers;
    /**
     * @param PhpParserNodeMapperInterface[] $phpParserNodeMappers
     */
    public function __construct(iterable $phpParserNodeMappers)
    {
        $this->phpParserNodeMappers = $phpParserNodeMappers;
    }
    public function mapToPHPStanType(Node $node) : Type
    {
        $nameOrExpr = $this->expandedNamespacedName($node);
        foreach ($this->phpParserNodeMappers as $phpParserNodeMapper) {
            if (!\is_a($nameOrExpr, $phpParserNodeMapper->getNodeType())) {
                continue;
            }
            // do not let Expr collect all the types
            // note: can be solve later with priorities on mapper interface, making this last
            if ($phpParserNodeMapper->getNodeType() !== Expr::class) {
                return $phpParserNodeMapper->mapToPHPStan($nameOrExpr);
            }
            if (!$nameOrExpr instanceof String_) {
                return $phpParserNodeMapper->mapToPHPStan($nameOrExpr);
            }
        }
        throw new NotImplementedYetException(\get_class($nameOrExpr));
    }
    /**
     * @return \PhpParser\Node|\PhpParser\Node\Name\FullyQualified
     */
    private function expandedNamespacedName(Node $node)
    {
        if (\get_class($node) === Name::class && $node->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            return new FullyQualified($node->getAttribute(AttributeKey::NAMESPACED_NAME));
        }
        return $node;
    }
}
