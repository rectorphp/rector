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
final class SimplifyIfNullableReturnRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\CodeQuality\TypeResolver\AssignVariableTypeResolver $assignVariableTypeResolver, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover)
    {
        $this->ifManipulator = $ifManipulator;
        $this->assignVariableTypeResolver = $assignVariableTypeResolver;
        $this->varTagRemover = $varTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Direct return on if nullable check before return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var BooleanNot|Instanceof_ $cond */
        $cond = $node->cond;
        /** @var Instanceof_ $instanceof */
        $instanceof = $cond instanceof \PhpParser\Node\Expr\BooleanNot ? $cond->expr : $cond;
        $variable = $instanceof->expr;
        $class = $instanceof->class;
        if (!$class instanceof \PhpParser\Node\Name) {
            return null;
        }
        /** @var Return_ $returnIfStmt */
        $returnIfStmt = $node->stmts[0];
        if ($this->isIfStmtReturnIncorrect($cond, $variable, $returnIfStmt)) {
            return null;
        }
        $previous = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_NODE);
        if (!$previous instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $previousAssign = $previous->expr;
        if (!$previousAssign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($previousAssign->var, $variable)) {
            return null;
        }
        /** @var Return_ $next */
        $next = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($this->isNextReturnIncorrect($cond, $variable, $next)) {
            return null;
        }
        $variableType = $this->assignVariableTypeResolver->resolve($previousAssign);
        if (!$variableType instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        $className = $class->toString();
        $types = $variableType->getTypes();
        return $this->processSimplifyNullableReturn($variableType, $types, $className, $next, $previous, $previousAssign->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_ $expr
     */
    private function isIfStmtReturnIncorrect($expr, \PhpParser\Node\Expr $variable, \PhpParser\Node\Stmt\Return_ $return) : bool
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot && !$this->valueResolver->isNull($return->expr)) {
            return \true;
        }
        return $expr instanceof \PhpParser\Node\Expr\Instanceof_ && !$this->nodeComparator->areNodesEqual($variable, $return->expr);
    }
    /**
     * @param \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_ $expr
     */
    private function isNextReturnIncorrect($expr, \PhpParser\Node\Expr $variable, \PhpParser\Node\Stmt\Return_ $return) : bool
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\BooleanNot && !$this->nodeComparator->areNodesEqual($return->expr, $variable)) {
            return \true;
        }
        return $expr instanceof \PhpParser\Node\Expr\Instanceof_ && !$this->valueResolver->isNull($return->expr);
    }
    /**
     * @param Type[] $types
     */
    private function processSimplifyNullableReturn(\PHPStan\Type\UnionType $unionType, array $types, string $className, \PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Stmt\Return_
    {
        if (\count($types) > 2) {
            return null;
        }
        if ($types[0] instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $types[1] instanceof \PHPStan\Type\NullType && $className === $types[0]->getClassName()) {
            return $this->removeAndReturn($return, $expression, $expr, $unionType);
        }
        if ($types[0] instanceof \PHPStan\Type\NullType && $types[1] instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && $className === $types[1]->getClassName()) {
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
        if (!$types[0] instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if (!$types[1] instanceof \PHPStan\Type\NullType) {
            return \true;
        }
        return $className !== $types[0]->getClassName();
    }
    private function removeAndReturn(\PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\Stmt\Expression $expression, \PhpParser\Node\Expr $expr, \PHPStan\Type\UnionType $unionType) : \PhpParser\Node\Stmt\Return_
    {
        $this->removeNode($return);
        $this->removeNode($expression);
        $return = new \PhpParser\Node\Stmt\Return_($expr);
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($expression, $unionType);
        $this->mirrorComments($return, $expression);
        return $return;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if (!$this->ifManipulator->isIfWithOnly($if, \PhpParser\Node\Stmt\Return_::class)) {
            return \true;
        }
        $next = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$next instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        $cond = $if->cond;
        if (!$cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            return !$cond instanceof \PhpParser\Node\Expr\Instanceof_;
        }
        return !$cond->expr instanceof \PhpParser\Node\Expr\Instanceof_;
    }
}
