<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
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
use Rector\Core\Util\ArrayChecker;
use Rector\Core\ValueObject\Application\File;
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
    /**
     * @readonly
     * @var \Rector\Core\Util\ArrayChecker
     */
    private $arrayChecker;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\VariableManipulator $variableManipulator, NodeComparator $nodeComparator, ReflectionResolver $reflectionResolver, \Rector\Core\NodeManipulator\ArrayDestructVariableFilter $arrayDestructVariableFilter, ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer, ArrayChecker $arrayChecker)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableManipulator = $variableManipulator;
        $this->nodeComparator = $nodeComparator;
        $this->reflectionResolver = $reflectionResolver;
        $this->arrayDestructVariableFilter = $arrayDestructVariableFilter;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
        $this->arrayChecker = $arrayChecker;
    }
    /**
     * @return Assign[]
     */
    public function collectReadyOnlyAssignScalarVariables(ClassMethod $classMethod, File $file) : array
    {
        $assignsOfScalarOrArrayToVariable = $this->variableManipulator->collectScalarOrArrayAssignsOfVariable($classMethod);
        // filter out [$value] = $array, array destructing
        $readOnlyVariableAssigns = $this->arrayDestructVariableFilter->filterOut($assignsOfScalarOrArrayToVariable, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutReferencedVariables($readOnlyVariableAssigns, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutMultiAssigns($readOnlyVariableAssigns);
        $readOnlyVariableAssigns = $this->filterOutForeachVariables($readOnlyVariableAssigns);
        $readOnlyVariableAssigns = $this->filterOutUsedByEncapsed($readOnlyVariableAssigns);
        /**
         * Remove unused variable assign is task of RemoveUnusedVariableAssignRector
         * so no need to move to constant early
         */
        $readOnlyVariableAssigns = $this->filterOutNeverUsedNext($readOnlyVariableAssigns);
        return $this->variableManipulator->filterOutChangedVariables($readOnlyVariableAssigns, $classMethod);
    }
    public function addParameterAndAssignToMethod(ClassMethod $classMethod, string $name, ?Type $type, Assign $assign) : void
    {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }
        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new Expression($assign);
        $classMethodHash = \spl_object_hash($classMethod);
        $this->alreadyAddedClassMethodNames[$classMethodHash][] = $name;
    }
    /**
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutUsedByEncapsed(array $readOnlyVariableAssigns) : array
    {
        $callable = function (Assign $readOnlyVariableAssign) : bool {
            $variable = $readOnlyVariableAssign->var;
            return !(bool) $this->betterNodeFinder->findFirstNext($readOnlyVariableAssign, function (Node $node) use($variable) : bool {
                if (!$node instanceof Encapsed) {
                    return \false;
                }
                return $this->arrayChecker->doesExist($node->parts, function (Expr $expr) use($variable) : bool {
                    return $this->nodeComparator->areNodesEqual($expr, $variable);
                });
            });
        };
        return \array_filter($readOnlyVariableAssigns, $callable);
    }
    /**
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutNeverUsedNext(array $readOnlyVariableAssigns) : array
    {
        return \array_filter($readOnlyVariableAssigns, function (Assign $assign) : bool {
            return $this->exprUsedInNextNodeAnalyzer->isUsed($assign->var);
        });
    }
    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutReferencedVariables(array $variableAssigns, ClassMethod $classMethod) : array
    {
        $referencedVariables = $this->collectReferenceVariableNames($classMethod);
        return \array_filter($variableAssigns, function (Assign $assign) use($referencedVariables) : bool {
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
        return \array_filter($readOnlyVariableAssigns, static function (Assign $assign) : bool {
            $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
            return !$parentNode instanceof Assign;
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
            if (!$foreach instanceof Foreach_) {
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
    private function hasMethodParameter(ClassMethod $classMethod, string $name) : bool
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
    private function collectReferenceVariableNames(ClassMethod $classMethod) : array
    {
        $referencedVariables = [];
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf($classMethod, Variable::class);
        foreach ($variables as $variable) {
            if ($this->nodeNameResolver->isName($variable, 'this')) {
                continue;
            }
            $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode !== null && $this->isExplicitlyReferenced($parentNode)) {
                $variableName = $this->nodeNameResolver->getName($variable);
                if ($variableName === null) {
                    continue;
                }
                $referencedVariables[] = $variableName;
                continue;
            }
            $argumentPosition = null;
            if ($parentNode instanceof Arg) {
                $argumentPosition = $parentNode->getAttribute(AttributeKey::ARGUMENT_POSITION);
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            }
            if (!$parentNode instanceof Node) {
                continue;
            }
            if ($argumentPosition === null) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variable);
            if ($variableName === null) {
                continue;
            }
            if (!$this->isCallOrConstructorWithReference($parentNode, $variable, $argumentPosition)) {
                continue;
            }
            $referencedVariables[] = $variableName;
        }
        return $referencedVariables;
    }
    private function findParentForeach(Assign $assign) : ?Foreach_
    {
        /** @var Foreach_|FunctionLike|null $foundNode */
        $foundNode = $this->betterNodeFinder->findParentByTypes($assign, [Foreach_::class, FunctionLike::class]);
        if (!$foundNode instanceof Foreach_) {
            return null;
        }
        return $foundNode;
    }
    private function isExplicitlyReferenced(Node $node) : bool
    {
        if ($node instanceof Arg || $node instanceof ClosureUse || $node instanceof Param) {
            return $node->byRef;
        }
        return \false;
    }
    private function isCallOrConstructorWithReference(Node $node, Variable $variable, int $argumentPosition) : bool
    {
        if ($this->isMethodCallWithReferencedArgument($node, $variable)) {
            return \true;
        }
        if ($this->isFuncCallWithReferencedArgument($node, $variable)) {
            return \true;
        }
        return $this->isConstructorWithReference($node, $argumentPosition);
    }
    private function isMethodCallWithReferencedArgument(Node $node, Variable $variable) : bool
    {
        if (!$node instanceof MethodCall) {
            return \false;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($node);
        if (!$methodReflection instanceof MethodReflection) {
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
    private function isFuncCallWithReferencedArgument(Node $node, Variable $variable) : bool
    {
        if (!$node instanceof FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isNames($node, ['array_shift', '*sort'])) {
            return \false;
        }
        if (!isset($node->args[0])) {
            return \false;
        }
        if (!$node->args[0] instanceof Arg) {
            return \false;
        }
        // is 1t argument
        return $node->args[0]->value !== $variable;
    }
    private function isConstructorWithReference(Node $node, int $argumentPosition) : bool
    {
        if (!$node instanceof New_) {
            return \false;
        }
        return $this->isParameterReferencedInMethodReflection($node, $argumentPosition);
    }
    private function isParameterReferencedInMethodReflection(New_ $new, int $argumentPosition) : bool
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (!$methodReflection instanceof MethodReflection) {
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
