<?php

declare (strict_types=1);
namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;
final class AssignAndRootExpr extends \Rector\Defluent\ValueObject\AbstractRootExpr implements \Rector\Defluent\Contract\ValueObject\RootExprAwareInterface, \Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface
{
    /**
     * @var \PhpParser\Node\Expr\Variable|null
     */
    private $silentVariable;
    public function __construct(\PhpParser\Node\Expr $assignExpr, \PhpParser\Node\Expr $rootExpr, ?\PhpParser\Node\Expr\Variable $silentVariable = null, bool $isFirstCallFactory = \false)
    {
        $this->silentVariable = $silentVariable;
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
    public function getSilentVariable() : ?\PhpParser\Node\Expr\Variable
    {
        return $this->silentVariable;
    }
    public function getReturnSilentVariable() : \PhpParser\Node\Stmt\Return_
    {
        if (!$this->silentVariable instanceof \PhpParser\Node\Expr\Variable) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return new \PhpParser\Node\Stmt\Return_($this->silentVariable);
    }
    public function getCallerExpr() : \PhpParser\Node\Expr
    {
        if ($this->silentVariable !== null) {
            return $this->silentVariable;
        }
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
            return $this->getCallerExpr();
        }
        return $firstAssign->var;
    }
}
