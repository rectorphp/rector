<?php

declare (strict_types=1);
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
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\CodeQuality\TypeResolver\AssignVariableTypeResolver;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
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
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\CodeQuality\TypeResolver\AssignVariableTypeResolver
     */
    private $assignVariableTypeResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    public function __construct(IfManipulator $ifManipulator, AssignVariableTypeResolver $assignVariableTypeResolver, VarTagRemover $varTagRemover)
    {
        $this->ifManipulator = $ifManipulator;
        $this->assignVariableTypeResolver = $assignVariableTypeResolver;
        $this->varTagRemover = $varTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Direct return on if nullable check before return', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->foo->bar();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var BooleanNot|Instanceof_ $cond */
        $cond = $node->cond;
        /** @var Instanceof_ $instanceof */
        $instanceof = $cond instanceof BooleanNot ? $cond->expr : $cond;
        $variable = $instanceof->expr;
        $class = $instanceof->class;
        if (!$class instanceof Name) {
            return null;
        }
        /** @var Return_ $returnIfStmt */
        $returnIfStmt = $node->stmts[0];
        if ($this->isIfStmtReturnIncorrect($cond, $variable, $returnIfStmt)) {
            return null;
        }
        $previous = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$previous instanceof Expression) {
            return null;
        }
        $previousAssign = $previous->expr;
        if (!$previousAssign instanceof Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($previousAssign->var, $variable)) {
            return null;
        }
        /** @var Return_ $nextNode */
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if ($this->isNextReturnIncorrect($cond, $variable, $nextNode)) {
            return null;
        }
        $variableType = $this->assignVariableTypeResolver->resolve($previousAssign);
        if (!$variableType instanceof UnionType) {
            return null;
        }
        $className = $class->toString();
        $types = $variableType->getTypes();
        return $this->processSimplifyNullableReturn($variableType, $types, $className, $nextNode, $previous, $previousAssign->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_ $expr
     */
    private function isIfStmtReturnIncorrect($expr, Expr $variable, Return_ $return) : bool
    {
        if (!$return->expr instanceof Expr) {
            return \true;
        }
        if ($expr instanceof BooleanNot && !$this->valueResolver->isNull($return->expr)) {
            return \true;
        }
        return $expr instanceof Instanceof_ && !$this->nodeComparator->areNodesEqual($variable, $return->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_ $expr
     */
    private function isNextReturnIncorrect($expr, Expr $variable, Return_ $return) : bool
    {
        if (!$return->expr instanceof Expr) {
            return \true;
        }
        if ($expr instanceof BooleanNot && !$this->nodeComparator->areNodesEqual($return->expr, $variable)) {
            return \true;
        }
        return $expr instanceof Instanceof_ && !$this->valueResolver->isNull($return->expr);
    }
    /**
     * @param Type[] $types
     */
    private function processSimplifyNullableReturn(UnionType $unionType, array $types, string $className, Return_ $return, Expression $expression, Expr $expr) : ?Return_
    {
        if (\count($types) > 2) {
            return null;
        }
        if ($types[0] instanceof FullyQualifiedObjectType && $types[1] instanceof NullType && $className === $types[0]->getClassName()) {
            return $this->removeAndReturn($return, $expression, $expr, $unionType);
        }
        if ($types[0] instanceof NullType && $types[1] instanceof FullyQualifiedObjectType && $className === $types[1]->getClassName()) {
            return $this->removeAndReturn($return, $expression, $expr, $unionType);
        }
        if ($this->isNotTypedNullable($types, $className)) {
            return null;
        }
        return $this->removeAndReturn($return, $expression, $expr, $unionType);
    }
    /**
     * @param Type[] $types
     */
    private function isNotTypedNullable(array $types, string $className) : bool
    {
        if (!$types[0] instanceof ObjectType) {
            return \true;
        }
        if (!$types[1] instanceof NullType) {
            return \true;
        }
        return $className !== $types[0]->getClassName();
    }
    private function removeAndReturn(Return_ $return, Expression $expression, Expr $expr, UnionType $unionType) : Return_
    {
        $this->removeNode($return);
        $this->removeNode($expression);
        $exprReturn = new Return_($expr);
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($expression, $unionType);
        $this->mirrorComments($exprReturn, $expression);
        return $exprReturn;
    }
    private function shouldSkip(If_ $if) : bool
    {
        if (!$this->ifManipulator->isIfWithOnly($if, Return_::class)) {
            return \true;
        }
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof Return_) {
            return \true;
        }
        $cond = $if->cond;
        if (!$cond instanceof BooleanNot) {
            return !$cond instanceof Instanceof_;
        }
        return !$cond->expr instanceof Instanceof_;
    }
}
