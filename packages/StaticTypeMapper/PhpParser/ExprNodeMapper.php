<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
final class ExprNodeMapper implements \Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType() : string
    {
        return \PhpParser\Node\Expr::class;
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function mapToPHPStan($node) : \PHPStan\Type\Type
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \PHPStan\Type\MixedType();
        }
        return $scope->getType($node);
    }
}
