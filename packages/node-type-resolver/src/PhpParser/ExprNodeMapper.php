<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ExprNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @param Expr $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return new MixedType();
        }

        return $scope->getType($node);
    }
}
