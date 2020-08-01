<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\NodeManipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

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

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        PropertyNaming $propertyNaming,
        VariableNaming $variableNaming
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableNaming = $variableNaming;
    }

    /**
     * @param MethodCall[] $methodCalls
     */
    public function extractFromMethodCalls(array $methodCalls, string $kind): ?AssignAndRootExpr
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof Variable) {
                return new AssignAndRootExpr($methodCall->var, $methodCall->var);
            }

            if ($methodCall->var instanceof PropertyFetch) {
                return new AssignAndRootExpr($methodCall->var, $methodCall->var);
            }

            if ($methodCall->var instanceof New_) {
                // direct = no parent
                if ($kind === 'in_args') {
                    $variableName = $this->variableNaming->resolveFromNode($methodCall->var);
                    $silentVariable = new Variable($variableName);
                    return new AssignAndRootExpr($methodCall->var, $methodCall->var, $silentVariable);
                }

                return $this->matchMethodCallOnNew($methodCall);
            }
        }

        return null;
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
            $variableName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
            if ($variableName === null) {
                return null;
            }

            $variable = new Variable($variableName);
            return new AssignAndRootExpr($methodCall->var, $methodCall->var, $variable);
        }

        // no assign, just standalone call
        return null;
    }
}
