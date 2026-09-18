<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\ValueObject\ExplicitBoolCondition;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ExplicitBoolConditionResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Expr\Ternary $node
     */
    public function resolve($node): ?ExplicitBoolCondition
    {
        // skip short ternary
        if ($node instanceof Ternary && !$node->if instanceof Expr) {
            return null;
        }
        if ($node->cond instanceof BooleanNot) {
            $conditionNode = $node->cond->expr;
            $isNegated = \true;
        } else {
            $conditionNode = $node->cond;
            $isNegated = \false;
        }
        if ($conditionNode instanceof Bool_) {
            return null;
        }
        $conditionStaticType = $this->nodeTypeResolver->getNativeType($conditionNode);
        if ($conditionStaticType instanceof MixedType || $conditionStaticType->isBoolean()->yes()) {
            return null;
        }
        return new ExplicitBoolCondition($conditionNode, $isNegated);
    }
}
