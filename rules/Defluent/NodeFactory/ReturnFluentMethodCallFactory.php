<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
use Rector\Defluent\ValueObject\FluentMethodCalls;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ReturnFluentMethodCallFactory
{
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor
     */
    private $fluentChainMethodCallRootExtractor;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming)
    {
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyNaming = $propertyNaming;
    }
    public function createFromFluentMethodCalls(\Rector\Defluent\ValueObject\FluentMethodCalls $fluentMethodCalls) : ?\Rector\Defluent\ValueObject\FirstAssignFluentCall
    {
        $rootMethodCall = $fluentMethodCalls->getRootMethodCall();
        // this means the 1st method creates different object then it runs on
        // e.g. $sheet->getRow(), creates a "Row" object
        $isFirstMethodCallFactory = $this->fluentChainMethodCallRootExtractor->isFirstMethodCallFactory($rootMethodCall);
        $lastMethodCall = $fluentMethodCalls->getRootMethodCall();
        if ($lastMethodCall->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $assignExpr = $lastMethodCall->var;
        } else {
            // we need a variable to assign the stuff into
            // the method call, does not belong to the
            $staticType = $this->nodeTypeResolver->getType($rootMethodCall);
            if (!$staticType instanceof \PHPStan\Type\ObjectType) {
                return null;
            }
            $variableName = $this->propertyNaming->fqnToVariableName($staticType);
            $assignExpr = new \PhpParser\Node\Expr\Variable($variableName);
        }
        return new \Rector\Defluent\ValueObject\FirstAssignFluentCall($assignExpr, $rootMethodCall, $isFirstMethodCallFactory, $fluentMethodCalls);
    }
}
