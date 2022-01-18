<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassMethodAssignManipulator
{
    /**
     * @var array<string, string[]>
     */
    private $alreadyAddedClassMethodNames = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\VariableManipulator
     */
    private $variableManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayDestructVariableFilter
     */
    private $arrayDestructVariableFilter;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\VariableManipulator $variableManipulator, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Core\NodeManipulator\ArrayDestructVariableFilter $arrayDestructVariableFilter, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableManipulator = $variableManipulator;
        $this->nodeComparator = $nodeComparator;
        $this->reflectionResolver = $reflectionResolver;
        $this->arrayDestructVariableFilter = $arrayDestructVariableFilter;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
    }
    /**
     * @return Assign[]
     */
    public function collectReadyOnlyAssignScalarVariables(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $assignsOfScalarOrArrayToVariable = $this->variableManipulator->collectScalarOrArrayAssignsOfVariable($classMethod);
        // filter out [$value] = $array, array destructing
        $readOnlyVariableAssigns = $this->arrayDestructVariableFilter->filterOut($assignsOfScalarOrArrayToVariable, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutReferencedVariables($readOnlyVariableAssigns, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutMultiAssigns($readOnlyVariableAssigns);
        $readOnlyVariableAssigns = $this->filterOutForeachVariables($readOnlyVariableAssigns);
        /**
         * Remove unused variable assign is task of RemoveUnusedVariableAssignRector
         * so no need to move to constant early
         */
        $readOnlyVariableAssigns = $this->filterOutNeverUsedNext($readOnlyVariableAssigns);
        return $this->variableManipulator->filterOutChangedVariables($readOnlyVariableAssigns, $classMethod);
    }
    public function addParameterAndAssignToMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $name, ?\PHPStan\Type\Type $type, \PhpParser\Node\Expr\Assign $assign) : void
    {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }
        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($assign);
        $classMethodHash = \spl_object_hash($classMethod);
        $this->alreadyAddedClassMethodNames[$classMethodHash][] = $name;
    }
    /**
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutNeverUsedNext(array $readOnlyVariableAssigns) : array
    {
        return \array_filter($readOnlyVariableAssigns, function (\PhpParser\Node\Expr\Assign $assign) : bool {
            return $this->exprUsedInNextNodeAnalyzer->isUsed($assign->var);
        });
    }
    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutReferencedVariables(array $variableAssigns, \PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $referencedVariables = $this->collectReferenceVariableNames($classMethod);
        return \array_filter($variableAssigns, function (\PhpParser\Node\Expr\Assign $assign) use($referencedVariables) : bool {
            return !$this->nodeNameResolver->isNames($assign->var, $referencedVariables);
        });
    }
    /**
     * E.g. $a = $b = $c = '...';
     *
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutMultiAssigns(array $readOnlyVariableAssigns) : array
    {
        return \array_filter($readOnlyVariableAssigns, function (\PhpParser\Node\Expr\Assign $assign) : bool {
            $parent = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            return !$parent instanceof \PhpParser\Node\Expr\Assign;
        });
    }
    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutForeachVariables(array $variableAssigns) : array
    {
        foreach ($variableAssigns as $key => $variableAssign) {
            $foreach = $this->findParentForeach($variableAssign);
            if (!$foreach instanceof \PhpParser\Node\Stmt\Foreach_) {
                continue;
            }
            if ($this->nodeComparator->areNodesEqual($foreach->valueVar, $variableAssign->var)) {
                unset($variableAssigns[$key]);
                continue;
            }
            if ($this->nodeComparator->areNodesEqual($foreach->keyVar, $variableAssign->var)) {
                unset($variableAssigns[$key]);
            }
        }
        return $variableAssigns;
    }
    private function hasMethodParameter(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $name) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return \true;
            }
        }
        $classMethodHash = \spl_object_hash($classMethod);
        if (!isset($this->alreadyAddedClassMethodNames[$classMethodHash])) {
            return \false;
        }
        return \in_array($name, $this->alreadyAddedClassMethodNames[$classMethodHash], \true);
    }
    /**
     * @return string[]
     */
    private function collectReferenceVariableNames(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $referencedVariables = [];
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Expr\Variable::class);
        foreach ($variables as $variable) {
            if ($this->nodeNameResolver->isName($variable, 'this')) {
                continue;
            }
            $parent = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parent !== null && $this->isExplicitlyReferenced($parent)) {
                $variableName = $this->nodeNameResolver->getName($variable);
                if ($variableName === null) {
                    continue;
                }
                $referencedVariables[] = $variableName;
                continue;
            }
            $argumentPosition = null;
            if ($parent instanceof \PhpParser\Node\Arg) {
                $argumentPosition = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ARGUMENT_POSITION);
                $parent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            }
            if (!$parent instanceof \PhpParser\Node) {
                continue;
            }
            if ($argumentPosition === null) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variable);
            if ($variableName === null) {
                continue;
            }
            if (!$this->isCallOrConstructorWithReference($parent, $variable, $argumentPosition)) {
                continue;
            }
            $referencedVariables[] = $variableName;
        }
        return $referencedVariables;
    }
    private function findParentForeach(\PhpParser\Node\Expr\Assign $assign) : ?\PhpParser\Node\Stmt\Foreach_
    {
        /** @var Foreach_|FunctionLike|null $foundNode */
        $foundNode = $this->betterNodeFinder->findFirstPreviousOfTypes($assign, [\PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\FunctionLike::class]);
        if (!$foundNode instanceof \PhpParser\Node\Stmt\Foreach_) {
            return null;
        }
        return $foundNode;
    }
    private function isExplicitlyReferenced(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Arg || $node instanceof \PhpParser\Node\Expr\ClosureUse || $node instanceof \PhpParser\Node\Param) {
            return $node->byRef;
        }
        return \false;
    }
    private function isCallOrConstructorWithReference(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable, int $argumentPosition) : bool
    {
        if ($this->isMethodCallWithReferencedArgument($node, $variable)) {
            return \true;
        }
        if ($this->isFuncCallWithReferencedArgument($node, $variable)) {
            return \true;
        }
        return $this->isConstructorWithReference($node, $argumentPosition);
    }
    private function isMethodCallWithReferencedArgument(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($node);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \false;
        }
        $variableName = $this->nodeNameResolver->getName($variable);
        foreach ($methodReflection->getVariants() as $parametersAcceptor) {
            foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
                if ($parameterReflection->getName() !== $variableName) {
                    continue;
                }
                return $parameterReflection->passedByReference()->yes();
            }
        }
        return \false;
    }
    /**
     * Matches e.g:
     * - array_shift($value)
     * - sort($values)
     */
    private function isFuncCallWithReferencedArgument(\PhpParser\Node $node, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isNames($node, ['array_shift', '*sort'])) {
            return \false;
        }
        if (!isset($node->args[0])) {
            return \false;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        // is 1t argument
        return $node->args[0]->value !== $variable;
    }
    private function isConstructorWithReference(\PhpParser\Node $node, int $argumentPosition) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\New_) {
            return \false;
        }
        return $this->isParameterReferencedInMethodReflection($node, $argumentPosition);
    }
    private function isParameterReferencedInMethodReflection(\PhpParser\Node\Expr\New_ $new, int $argumentPosition) : bool
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \false;
        }
        foreach ($methodReflection->getVariants() as $parametersAcceptor) {
            /** @var ParameterReflection $parameterReflection */
            foreach ($parametersAcceptor->getParameters() as $parameterPosition => $parameterReflection) {
                if ($parameterPosition !== $argumentPosition) {
                    continue;
                }
                return $parameterReflection->passedByReference()->yes();
            }
        }
        return \false;
    }
}
