<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;

final class ExprNodeMapper implements PhpParserNodeMapperInterface
{
    /**
<<<<<<< HEAD
     * @return class-string<Node>
=======
     * @return class-string<\PhpParser\Node>
>>>>>>> 1b83ff428... add return type class string
     */
    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @param Expr $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return new MixedType();
        }

        return $scope->getType($node);
    }
}
