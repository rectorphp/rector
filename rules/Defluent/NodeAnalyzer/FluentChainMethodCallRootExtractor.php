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
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\Naming\VariableNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

/**
 * @see \Rector\Tests\Defluent\NodeFactory\FluentChainMethodCallRootExtractor\FluentChainMethodCallRootExtractorTest
 */
final class FluentChainMethodCallRootExtractor
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private PropertyNaming $propertyNaming,
        private VariableNaming $variableNaming,
        private ExprStringTypeResolver $exprStringTypeResolver,
        private NodeTypeResolver $nodeTypeResolver
    ) {
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
            if ($methodCall->var instanceof Variable || $methodCall->var instanceof PropertyFetch) {
                return $this->createAssignAndRootExprForVariableOrPropertyFetch($methodCall);
            }

            if ($methodCall->var instanceof New_) {
                // direct = no parent
                if ($kind === FluentCallsKind::IN_ARGS) {
                    return $this->resolveKindInArgs($methodCall);
                }

                return $this->matchMethodCallOnNew($methodCall->var);
            }
        }

        return null;
    }

    /**
     * beware: fluent vs. factory
     * A. FLUENT: $cook->bake()->serve() // only "Cook"
     * B. FACTORY: $food = $cook->bake()->warmUp(); // only "Food"
     */
    public function isFirstMethodCallFactory(MethodCall $methodCall): bool
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
        $isFirstCallFactory = $this->isFirstMethodCallFactory($methodCall);

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

    private function matchMethodCallOnNew(New_ $new): ?AssignAndRootExpr
    {
        // we need assigned left variable here
        $previousAssignOrReturn = $this->betterNodeFinder->findFirstPreviousOfTypes(
            $new,
            [Assign::class, Return_::class]
        );

        if ($previousAssignOrReturn instanceof Assign) {
            return new AssignAndRootExpr($previousAssignOrReturn->var, $new);
        }

        if ($previousAssignOrReturn instanceof Return_) {
            $className = $this->nodeNameResolver->getName($new->class);
            if ($className === null) {
                return null;
            }

            $fullyQualifiedObjectType = new FullyQualifiedObjectType($className);
            $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
            if (! $expectedName instanceof ExpectedName) {
                return null;
            }

            $variable = new Variable($expectedName->getName());
            return new AssignAndRootExpr($new, $new, $variable);
        }

        // no assign, just standalone call
        return null;
    }
}
