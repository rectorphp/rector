<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
final class VariableManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\ReadWrite\Guard\VariableToConstantGuard
     */
    private $variableToConstantGuard;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\AssignManipulator $assignManipulator, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, VariableToConstantGuard $variableToConstantGuard, NodeComparator $nodeComparator, ExprAnalyzer $exprAnalyzer)
    {
        $this->assignManipulator = $assignManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->nodeComparator = $nodeComparator;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    /**
     * @return Assign[]
     */
    public function collectScalarOrArrayAssignsOfVariable(ClassMethod $classMethod) : array
    {
        $currentClass = $this->betterNodeFinder->findParentType($classMethod, Class_::class);
        if (!$currentClass instanceof Class_) {
            return [];
        }
        $currentClassName = (string) $this->nodeNameResolver->getName($currentClass);
        $assignsOfArrayToVariable = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use(&$assignsOfArrayToVariable, $currentClass, $currentClassName) {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if ($this->exprAnalyzer->isDynamicExpr($node->expr)) {
                return null;
            }
            if ($this->hasEncapsedStringPart($node->expr)) {
                return null;
            }
            if ($this->isTestCaseExpectedVariable($node->var)) {
                return null;
            }
            if ($node->expr instanceof ConstFetch) {
                return null;
            }
            if ($node->expr instanceof ClassConstFetch && $this->isOutsideClass($node->expr, $currentClass, $currentClassName)) {
                return null;
            }
            $assignsOfArrayToVariable[] = $node;
        });
        return $assignsOfArrayToVariable;
    }
    /**
     * @param Assign[] $assignsOfArrayToVariable
     * @return Assign[]
     */
    public function filterOutChangedVariables(array $assignsOfArrayToVariable, ClassMethod $classMethod) : array
    {
        return \array_filter($assignsOfArrayToVariable, function (Assign $assign) use($classMethod) : bool {
            return $this->isReadOnlyVariable($classMethod, $assign);
        });
    }
    private function isOutsideClass(ClassConstFetch $classConstFetch, Class_ $currentClass, string $currentClassName) : bool
    {
        /**
         * Dynamic class already checked on $this->exprAnalyzer->isDynamicValue() early
         * @var Name $class
         */
        $class = $classConstFetch->class;
        if ($this->nodeNameResolver->isName($class, 'self')) {
            return $currentClass->extends instanceof FullyQualified;
        }
        return !$this->nodeNameResolver->isName($class, $currentClassName);
    }
    private function hasEncapsedStringPart(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, static function (Node $subNode) : bool {
            return $subNode instanceof Encapsed || $subNode instanceof EncapsedStringPart;
        });
    }
    private function isTestCaseExpectedVariable(Variable $variable) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($variable, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        if (\substr_compare($className, 'Test', -\strlen('Test')) !== 0) {
            return \false;
        }
        return $this->nodeNameResolver->isName($variable, 'expect*');
    }
    /**
     * Inspiration
     * @see \Rector\Core\NodeManipulator\PropertyManipulator::isPropertyUsedInReadContext()
     */
    private function isReadOnlyVariable(ClassMethod $classMethod, Assign $assign) : bool
    {
        if (!$assign->var instanceof Variable) {
            return \false;
        }
        $variableUsages = $this->collectVariableUsages($classMethod, $assign->var, $assign);
        foreach ($variableUsages as $variableUsage) {
            $parentNode = $variableUsage->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Arg && !$this->variableToConstantGuard->isReadArg($parentNode)) {
                return \false;
            }
            if (!$this->assignManipulator->isLeftPartOfAssign($variableUsage)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    /**
     * @return Variable[]
     */
    private function collectVariableUsages(ClassMethod $classMethod, Variable $variable, Assign $assign) : array
    {
        return $this->betterNodeFinder->find((array) $classMethod->getStmts(), function (Node $node) use($variable, $assign) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            // skip initialization
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode === $assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }
}
