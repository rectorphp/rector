<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/41000866/1348344 https://3v4l.org/ABDNv
 *
 * @see \Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\AssignArrayToStringRectorTest
 */
final class AssignArrayToStringRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_ASSIGN_ARRAY_TO_STRING;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('String cannot be turned into array by assignment anymore', [new CodeSample(<<<'CODE_SAMPLE'
$string = '';
$string[] = 1;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$string = [];
$string[] = 1;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class, Property::class];
    }
    /**
     * @param Assign|Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $defaultExpr = $this->resolveDefaultValueExpr($node);
        if (!$defaultExpr instanceof Expr) {
            return null;
        }
        if (!$this->isEmptyString($defaultExpr)) {
            return null;
        }
        $assignedVar = $this->resolveAssignedVar($node);
        // 1. variable!
        $shouldRetype = \false;
        /** @var array<Variable|PropertyFetch|StaticPropertyFetch> $exprUsages */
        $exprUsages = $this->betterNodeFinder->findSameNamedExprs($assignedVar);
        // detect if is part of variable assign?
        foreach ($exprUsages as $exprUsage) {
            $parent = $exprUsage->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parent instanceof ArrayDimFetch) {
                continue;
            }
            $firstAssign = $this->betterNodeFinder->findParentType($parent, Assign::class);
            if (!$firstAssign instanceof Assign) {
                continue;
            }
            // skip explicit assigns
            if ($parent->dim !== null) {
                continue;
            }
            $shouldRetype = \true;
            break;
        }
        if (!$shouldRetype) {
            return null;
        }
        if ($node instanceof Property) {
            $node->props[0]->default = new Array_();
            return $node;
        }
        $node->expr = new Array_();
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Stmt\Property $node
     */
    private function resolveDefaultValueExpr($node) : ?Expr
    {
        if ($node instanceof Property) {
            return $node->props[0]->default;
        }
        return $node->expr;
    }
    private function isEmptyString(Expr $expr) : bool
    {
        if (!$expr instanceof String_) {
            return \false;
        }
        return $expr->value === '';
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Stmt\Property $node
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Stmt\Property
     */
    private function resolveAssignedVar($node)
    {
        if ($node instanceof Assign) {
            return $node->var;
        }
        return $node;
    }
}
