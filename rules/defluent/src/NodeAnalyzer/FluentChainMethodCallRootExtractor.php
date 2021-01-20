<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\StaticInstanceOf;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * @see \Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor\FluentChainMethodCallRootExtractorTest
 */
final class FluentChainMethodCallRootExtractor
{
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

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        PropertyNaming $propertyNaming,
        VariableNaming $variableNaming,
        ExprStringTypeResolver $exprStringTypeResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableNaming = $variableNaming;
        $this->exprStringTypeResolver = $exprStringTypeResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param MethodCall[] $methodCalls
     */
    public function extractFromMethodCalls(array $methodCalls, string $kind): ?AssignAndRootExpr
    {
        // we need at least 2 method call for fluent
        if (count($methodCalls) < 2) {
            return null;
        }

        foreach ($methodCalls as $methodCall) {
            if (StaticInstanceOf::isOneOf($methodCall->var, [Variable::class, PropertyFetch::class])) {
                return $this->createAssignAndRootExprForVariableOrPropertyFetch($methodCall);
            }
            if ($methodCall->var instanceof New_) {
                // direct = no parent
                if ($kind === FluentCallsKind::IN_ARGS) {
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
    public function resolveIsFirstMethodCallFactory(MethodCall $methodCall): bool
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

    private function createAssignAndRootExprForVariableOrPropertyFetch(MethodCall $methodCall): AssignAndRootExpr
    {
        $isFirstCallFactory = $this->resolveIsFirstMethodCallFactory($methodCall);

        // the method call, does not belong to the
        $staticType = $this->nodeTypeResolver->getStaticType($methodCall);
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        // no assign
        if ($parentNode instanceof Expression) {
            $variableName = $this->propertyNaming->fqnToVariableName($staticType);

            // the assign expresison must be break
            // pesuero code bsaed on type
            $variable = new Variable($variableName);
            return new AssignAndRootExpr($methodCall->var, $methodCall->var, $variable, $isFirstCallFactory);
        }

        return new AssignAndRootExpr($methodCall->var, $methodCall->var, null, $isFirstCallFactory);
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
            if (! $expectedName instanceof ExpectedName) {
                return null;
            }

            $variable = new Variable($expectedName->getName());
            return new AssignAndRootExpr($methodCall->var, $methodCall->var, $variable);
        }

        // no assign, just standalone call
        return null;
    }
}
