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
    public function __construct(array $phpParserNodeMappers)
    {
        $this->phpParserNodeMappers = $phpParserNodeMappers;
    }
    public function mapToPHPStanType(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        if (\get_class($node) === \PhpParser\Node\Name::class && $node->hasAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME)) {
            $node = new \PhpParser\Node\Name\FullyQualified($node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME));
        }
        foreach ($this->phpParserNodeMappers as $phpParserNodeMapper) {
            if (!\is_a($node, $phpParserNodeMapper->getNodeType())) {
                continue;
            }
            // do not let Expr collect all the types
            // note: can be solve later with priorities on mapper interface, making this last
            if ($phpParserNodeMapper->getNodeType() !== \PhpParser\Node\Expr::class) {
                return $phpParserNodeMapper->mapToPHPStan($node);
            }
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return $phpParserNodeMapper->mapToPHPStan($node);
            }
        }
        throw new \Rector\Core\Exception\NotImplementedYetException(\get_class($node));
    }
}
