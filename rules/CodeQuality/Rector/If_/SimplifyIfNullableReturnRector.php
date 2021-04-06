<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector\SimplifyIfNullableReturnRectorTest
 */
final class SimplifyIfNullableReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Direct return on if nullable check before return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();
        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        /** @var \stdClass|null $value */
        $value = $this->foo->bar();
        if (! $value instanceof \stdClass) {
            return null;
        }

        return $value;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var BooleanNot $cond */
        $cond = $node->cond;
        /** @var Instanceof_ $instanceof */
        $instanceof = $cond->expr;
        $variable = $instanceof->expr;
        $class = $instanceof->class;

        if (! $class instanceof Name) {
            return null;
        }

        $previous = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previous instanceof Expression) {
            return null;
        }

        $previousAssign = $previous->expr;
        if (! $previousAssign instanceof Assign && $this->nodeComparator->areNodesEqual(
            $previousAssign->var,
            $variable
        )) {
            return null;
        }

        /** @var Return_ $next */
        $next = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $this->nodeComparator->areNodesEqual($next->expr, $variable)) {
            return null;
        }

        $variableType = $this->nodeTypeResolver->resolve($previousAssign->var);
        $exprType = $this->nodeTypeResolver->resolve($previousAssign->expr);

        if ($exprType instanceof UnionType) {
            $variableType = $exprType;
        }

        if (! $variableType instanceof UnionType) {
            return null;
        }

        $types = $variableType->getTypes();
        if (count($types) > 2) {
            return null;
        }

        $className = $class->toString();
        if ($types[0] instanceof FullyQualifiedObjectType && $types[1] instanceof NullType && $className === $types[0]->getClassName()) {
            return $this->processSimplifyNullableReturn($next, $previous, $previousAssign->expr);
        }

        if ($types[0] instanceof NullType && $types[1] instanceof FullyQualifiedObjectType && $className === $types[1]->getClassName()) {
            return $this->processSimplifyNullableReturn($next, $previous, $previousAssign->expr);
        }

        if ($types[0] instanceof ObjectType && $types[1] instanceof NullType && $className === $types[0]->getClassName()) {
            return $this->processSimplifyNullableReturn($next, $previous, $previousAssign->expr);
        }

        return null;
    }

    private function processSimplifyNullableReturn(Return_ $return, Expression $expression, Expr $expr): Return_
    {
        $this->removeNode($return);
        $this->removeNode($expression);

        return new Return_($expr);
    }

    private function shouldSkip(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnly($if, Return_::class)) {
            return true;
        }

        $cond = $if->cond;

        if (! $cond instanceof BooleanNot) {
            return true;
        }

        if (! $cond->expr instanceof Instanceof_) {
            return true;
        }

        $next = $if->getAttribute(AttributeKey::NEXT_NODE);
        return ! $next instanceof Return_;
    }
}
