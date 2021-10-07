<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\ExprStringTypeResolver
     */
    private $exprStringTypeResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\Defluent\NodeAnalyzer\ExprStringTypeResolver $exprStringTypeResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyNaming = $propertyNaming;
        $this->variableNaming = $variableNaming;
        $this->exprStringTypeResolver = $exprStringTypeResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param MethodCall[] $methodCalls
     */
    public function extractFromMethodCalls(array $methodCalls, string $kind) : ?\Rector\Defluent\ValueObject\AssignAndRootExpr
    {
        // we need at least 2 method call for fluent
        if (\count($methodCalls) < 2) {
            return null;
        }
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof \PhpParser\Node\Expr\Variable || $methodCall->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return $this->createAssignAndRootExprForVariableOrPropertyFetch($methodCall);
            }
            if ($methodCall->var instanceof \PhpParser\Node\Expr\New_) {
                // direct = no parent
                if ($kind === \Rector\Defluent\ValueObject\FluentCallsKind::IN_ARGS) {
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
    public function isFirstMethodCallFactory(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        $variableStaticType = $this->exprStringTypeResolver->resolve($methodCall->var);
        $calledMethodStaticType = $this->exprStringTypeResolver->resolve($methodCall);
        // get next method call
        $nextMethodCall = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$nextMethodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        $nestedCallStaticType = $this->exprStringTypeResolver->resolve($nextMethodCall);
        if ($nestedCallStaticType === null) {
            return \false;
        }
        if ($nestedCallStaticType !== $calledMethodStaticType) {
            return \false;
        }
        return $variableStaticType !== $calledMethodStaticType;
    }
    private function createAssignAndRootExprForVariableOrPropertyFetch(\PhpParser\Node\Expr\MethodCall $methodCall) : \Rector\Defluent\ValueObject\AssignAndRootExpr
    {
        $isFirstCallFactory = $this->isFirstMethodCallFactory($methodCall);
        // the method call, does not belong to the
        $staticType = $this->nodeTypeResolver->getType($methodCall);
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // no assign
        if ($parentNode instanceof \PhpParser\Node\Stmt\Expression) {
            $variableName = $this->propertyNaming->fqnToVariableName($staticType);
            // the assign expresison must be break
            // pesuero code bsaed on type
            $variable = new \PhpParser\Node\Expr\Variable($variableName);
            return new \Rector\Defluent\ValueObject\AssignAndRootExpr($methodCall->var, $methodCall->var, $variable, $isFirstCallFactory);
        }
        return new \Rector\Defluent\ValueObject\AssignAndRootExpr($methodCall->var, $methodCall->var, null, $isFirstCallFactory);
    }
    private function resolveKindInArgs(\PhpParser\Node\Expr\MethodCall $methodCall) : \Rector\Defluent\ValueObject\AssignAndRootExpr
    {
        $variableName = $this->variableNaming->resolveFromNode($methodCall->var);
        if ($variableName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $silentVariable = new \PhpParser\Node\Expr\Variable($variableName);
        return new \Rector\Defluent\ValueObject\AssignAndRootExpr($methodCall->var, $methodCall->var, $silentVariable);
    }
    private function matchMethodCallOnNew(\PhpParser\Node\Expr\New_ $new) : ?\Rector\Defluent\ValueObject\AssignAndRootExpr
    {
        // we need assigned left variable here
        $previousAssignOrReturn = $this->betterNodeFinder->findFirstPreviousOfTypes($new, [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Stmt\Return_::class]);
        if ($previousAssignOrReturn instanceof \PhpParser\Node\Expr\Assign) {
            return new \Rector\Defluent\ValueObject\AssignAndRootExpr($previousAssignOrReturn->var, $new);
        }
        if ($previousAssignOrReturn instanceof \PhpParser\Node\Stmt\Return_) {
            $className = $this->nodeNameResolver->getName($new->class);
            if ($className === null) {
                return null;
            }
            $fullyQualifiedObjectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
            $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
            if (!$expectedName instanceof \Rector\Naming\ValueObject\ExpectedName) {
                return null;
            }
            $variable = new \PhpParser\Node\Expr\Variable($expectedName->getName());
            return new \Rector\Defluent\ValueObject\AssignAndRootExpr($new, $new, $variable);
        }
        // no assign, just standalone call
        return null;
    }
}
