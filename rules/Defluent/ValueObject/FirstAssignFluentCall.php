<?php

declare (strict_types=1);
namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;
final class FirstAssignFluentCall extends \Rector\Defluent\ValueObject\AbstractRootExpr implements \Rector\Defluent\Contract\ValueObject\RootExprAwareInterface, \Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface
{
    /**
     * @var \Rector\Defluent\ValueObject\FluentMethodCalls
     */
    private $fluentMethodCalls;
    public function __construct(\PhpParser\Node\Expr $assignExpr, \PhpParser\Node\Expr $rootExpr, bool $isFirstCallFactory, \Rector\Defluent\ValueObject\FluentMethodCalls $fluentMethodCalls)
    {
        $this->fluentMethodCalls = $fluentMethodCalls;
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
        $this->isFirstCallFactory = $isFirstCallFactory;
    }
    public function getAssignExpr() : \PhpParser\Node\Expr
    {
        return $this->assignExpr;
    }
    public function getRootExpr() : \PhpParser\Node\Expr
    {
        return $this->rootExpr;
    }
    public function getCallerExpr() : \PhpParser\Node\Expr
    {
        return $this->assignExpr;
    }
    public function isFirstCallFactory() : bool
    {
        return $this->isFirstCallFactory;
    }
    public function getFactoryAssignVariable() : \PhpParser\Node\Expr
    {
        $firstAssign = $this->getFirstAssign();
        if (!$firstAssign instanceof \PhpParser\Node\Expr\Assign) {
            return $this->assignExpr;
        }
        return $firstAssign->var;
    }
    /**
     * @return MethodCall[]
     */
    public function getFluentMethodCalls() : array
    {
        return $this->fluentMethodCalls->getFluentMethodCalls();
    }
}
