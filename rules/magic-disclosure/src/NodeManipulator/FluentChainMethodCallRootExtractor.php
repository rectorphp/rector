<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\MagicDisclosure\NodeAnalyzer\ExprStringTypeResolver;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class FluentChainMethodCallRootExtractor
{
    /**
     * @var string
     */
    public const KIND_IN_ARGS = 'in_args';

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var ExprStringTypeResolver
     */
    private $exprStringTypeResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        PropertyNaming $propertyNaming,
        VariableNaming $variableNaming,
        ExprStringTypeResolver $exprStringTypeResolver
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableNaming = $variableNaming;
        $this->exprStringTypeResolver = $exprStringTypeResolver;
    }

    /**
     * @param MethodCall[] $methodCalls
     */
    public function extractFromMethodCalls(array $methodCalls, string $kind): ?AssignAndRootExpr
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof Variable || $methodCall->var instanceof PropertyFetch) {
                $isFirstCallFactory = $this->resolveIsFirstCallFactory($methodCall);
                return new AssignAndRootExpr($methodCall->var, $methodCall->var, null, $isFirstCallFactory);
            }

            if ($methodCall->var instanceof New_) {
                // direct = no parent
                if ($kind === self::KIND_IN_ARGS) {
                    return $this->resolveKindInArgs($methodCall);
                }

                return $this->matchMethodCallOnNew($methodCall);
            }
        }

        return null;
    }

    /**
     * beware: fluent vs. factory
     * A. FLUENT: $cook->bake()->serve() // only "Cook"
     * B. FACTORY: $food = $cook->bake()->warmUp(); // only "Food"
     */
    private function resolveIsFirstCallFactory(MethodCall $methodCall): bool
    {
        $variableStaticType = $this->exprStringTypeResolver->resolve($methodCall->var);
        $calledMethodStaticType = $this->exprStringTypeResolver->resolve($methodCall);

        // get next method call
        $nextMethodCall = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $nextMethodCall instanceof MethodCall) {
            return false;
        }

        $nestedCallStaticType = $this->exprStringTypeResolver->resolve($nextMethodCall);
        if ($nestedCallStaticType === null) {
            return false;
        }

        if ($nestedCallStaticType !== $calledMethodStaticType) {
            return false;
        }

        return $variableStaticType !== $calledMethodStaticType;
    }

    private function resolveKindInArgs(MethodCall $methodCall): AssignAndRootExpr
    {
        $variableName = $this->variableNaming->resolveFromNode($methodCall->var);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        $silentVariable = new Variable($variableName);
        return new AssignAndRootExpr($methodCall->var, $methodCall->var, $silentVariable);
    }

    private function matchMethodCallOnNew(MethodCall $methodCall): ?AssignAndRootExpr
    {
        // we need assigned left variable here
        $previousAssignOrReturn = $this->betterNodeFinder->findFirstPreviousOfTypes(
            $methodCall->var,
            [Assign::class, Return_::class]
        );

        if ($previousAssignOrReturn instanceof Assign) {
            return new AssignAndRootExpr($previousAssignOrReturn->var, $methodCall->var);
        }

        if ($previousAssignOrReturn instanceof Return_) {
            $className = $this->nodeNameResolver->getName($methodCall->var->class);
            if ($className === null) {
                return null;
            }

            $fullyQualifiedObjectType = new FullyQualifiedObjectType($className);
            $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
            if ($expectedName === null) {
                return null;
            }

            $variable = new Variable($expectedName->getName());
            return new AssignAndRootExpr($methodCall->var, $methodCall->var, $variable);
        }

        // no assign, just standalone call
        return null;
    }
}
