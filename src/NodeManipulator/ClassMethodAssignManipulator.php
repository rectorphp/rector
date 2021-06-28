<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
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
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodAssignManipulator
{
    /**
     * @var array<string, string[]>
     */
    private array $alreadyAddedClassMethodNames = [];

    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeFactory $nodeFactory,
        private NodeNameResolver $nodeNameResolver,
        private VariableManipulator $variableManipulator,
        private NodeComparator $nodeComparator,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    /**
     * @return Assign[]
     */
    public function collectReadyOnlyAssignScalarVariables(ClassMethod $classMethod): array
    {
        $assignsOfScalarOrArrayToVariable = $this->variableManipulator->collectScalarOrArrayAssignsOfVariable(
            $classMethod
        );

        // filter out [$value] = $array, array destructing
        $readOnlyVariableAssigns = $this->filterOutArrayDestructedVariables(
            $assignsOfScalarOrArrayToVariable,
            $classMethod
        );

        $readOnlyVariableAssigns = $this->filterOutReferencedVariables($readOnlyVariableAssigns, $classMethod);
        $readOnlyVariableAssigns = $this->filterOutMultiAssigns($readOnlyVariableAssigns);
        $readOnlyVariableAssigns = $this->filterOutForeachVariables($readOnlyVariableAssigns);

        return $this->variableManipulator->filterOutChangedVariables($readOnlyVariableAssigns, $classMethod);
    }

    public function addParameterAndAssignToMethod(
        ClassMethod $classMethod,
        string $name,
        ?Type $type,
        Assign $assign
    ): void {
        if ($this->hasMethodParameter($classMethod, $name)) {
            return;
        }

        $classMethod->params[] = $this->nodeFactory->createParamFromNameAndType($name, $type);
        $classMethod->stmts[] = new Expression($assign);

        $classMethodHash = spl_object_hash($classMethod);
        $this->alreadyAddedClassMethodNames[$classMethodHash][] = $name;
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutArrayDestructedVariables(array $variableAssigns, ClassMethod $classMethod): array
    {
        $arrayDestructionCreatedVariables = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$arrayDestructionCreatedVariables
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof Array_ && ! $node->var instanceof List_) {
                return null;
            }

            foreach ($node->var->items as $arrayItem) {
                // empty item
                if ($arrayItem === null) {
                    continue;
                }

                if (! $arrayItem->value instanceof Variable) {
                    continue;
                }

                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($arrayItem->value);
                $arrayDestructionCreatedVariables[] = $variableName;
            }
        });

        return array_filter(
            $variableAssigns,
            fn (Assign $assign): bool => ! $this->nodeNameResolver->isNames(
                $assign->var,
                $arrayDestructionCreatedVariables
            )
        );
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutReferencedVariables(array $variableAssigns, ClassMethod $classMethod): array
    {
        $referencedVariables = $this->collectReferenceVariableNames($classMethod);

        return array_filter(
            $variableAssigns,
            fn (Assign $assign): bool => ! $this->nodeNameResolver->isNames($assign->var, $referencedVariables)
        );
    }

    /**
     * E.g. $a = $b = $c = '...';
     *
     * @param Assign[] $readOnlyVariableAssigns
     * @return Assign[]
     */
    private function filterOutMultiAssigns(array $readOnlyVariableAssigns): array
    {
        return array_filter($readOnlyVariableAssigns, function (Assign $assign): bool {
            $parent = $assign->getAttribute(AttributeKey::PARENT_NODE);
            return ! $parent instanceof Assign;
        });
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    private function filterOutForeachVariables(array $variableAssigns): array
    {
        foreach ($variableAssigns as $key => $variableAssign) {
            $foreach = $this->findParentForeach($variableAssign);
            if (! $foreach instanceof Foreach_) {
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

    private function hasMethodParameter(ClassMethod $classMethod, string $name): bool
    {
        foreach ($classMethod->params as $param) {
            if ($this->nodeNameResolver->isName($param->var, $name)) {
                return true;
            }
        }

        $classMethodHash = spl_object_hash($classMethod);
        if (! isset($this->alreadyAddedClassMethodNames[$classMethodHash])) {
            return false;
        }

        return in_array($name, $this->alreadyAddedClassMethodNames[$classMethodHash], true);
    }

    /**
     * @return string[]
     */
    private function collectReferenceVariableNames(ClassMethod $classMethod): array
    {
        $referencedVariables = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$referencedVariables
        ) {
            if (! $node instanceof Variable) {
                return null;
            }

            if ($this->nodeNameResolver->isName($node, 'this')) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode !== null && $this->isExplicitlyReferenced($parentNode)) {
                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($node);
                $referencedVariables[] = $variableName;
                return null;
            }

            $argumentPosition = null;
            if ($parentNode instanceof Arg) {
                $argumentPosition = $parentNode->getAttribute(AttributeKey::ARGUMENT_POSITION);
                $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            }

            if (! $parentNode instanceof Node) {
                return null;
            }

            if ($argumentPosition === null) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($node);

            if ($this->isCallOrConstructorWithReference($parentNode, $node, $argumentPosition)) {
                $referencedVariables[] = $variableName;
            }
        });

        return $referencedVariables;
    }

    private function findParentForeach(Assign $assign): ?Foreach_
    {
        /** @var Foreach_|FunctionLike|null $foundNode */
        $foundNode = $this->betterNodeFinder->findFirstPreviousOfTypes($assign, [Foreach_::class, FunctionLike::class]);
        if (! $foundNode instanceof Foreach_) {
            return null;
        }

        return $foundNode;
    }

    private function isExplicitlyReferenced(Node $node): bool
    {
        if (! property_exists($node, 'byRef')) {
            return false;
        }

        if ($node instanceof Arg || $node instanceof ClosureUse || $node instanceof Param) {
            return $node->byRef;
        }

        return false;
    }

    private function isCallOrConstructorWithReference(Node $node, Variable $variable, int $argumentPosition): bool
    {
        if ($this->isMethodCallWithReferencedArgument($node, $variable)) {
            return true;
        }

        if ($this->isFuncCallWithReferencedArgument($node, $variable)) {
            return true;
        }
        return $this->isConstructorWithReference($node, $argumentPosition);
    }

    private function isMethodCallWithReferencedArgument(Node $node, Variable $variable): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromMethodCall($node);
        if (! $methodReflection instanceof MethodReflection) {
            return false;
        }

        $variableName = $this->nodeNameResolver->getName($variable);
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            if ($parameterReflection->getName() !== $variableName) {
                continue;
            }

            return $parameterReflection->passedByReference()
                ->yes();
        }

        return false;
    }

    /**
     * Matches e.g:
     * - array_shift($value)
     * - sort($values)
     */
    private function isFuncCallWithReferencedArgument(Node $node, Variable $variable): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isNames($node, ['array_shift', '*sort'])) {
            return false;
        }

        // is 1t argument
        return $node->args[0]->value !== $variable;
    }

    private function isConstructorWithReference(Node $node, int $argumentPosition): bool
    {
        if (! $node instanceof New_) {
            return false;
        }

        return $this->isParameterReferencedInMethodReflection($node, $argumentPosition);
    }

    private function isParameterReferencedInMethodReflection(New_ $new, int $argumentPosition): bool
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($new);
        if (! $methodReflection instanceof MethodReflection) {
            return false;
        }

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        /** @var ParameterReflection $parameterReflection */
        foreach ($parametersAcceptor->getParameters() as $parameterPosition => $parameterReflection) {
            if ($parameterPosition !== $argumentPosition) {
                continue;
            }

            return $parameterReflection->passedByReference()
                ->yes();
        }

        return false;
    }
}
