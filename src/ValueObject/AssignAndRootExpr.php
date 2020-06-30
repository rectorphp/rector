<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;

final class AssignAndRootExpr
{
    /**
     * @var Expr
     */
    private $assignExpr;

    /**
     * @var Expr
     */
    private $rootExpr;

    /**
     * @var Expr\Variable|null
     */
    private $silentVariable;

    public function __construct(Expr $assignExpr, Expr $rootExpr, ?Expr\Variable $silentVariable = null)
    {
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
        $this->silentVariable = $silentVariable;
    }

    public function getAssignExpr(): Expr
    {
        return $this->assignExpr;
    }

    public function getFirstAssign(): Expr\Assign
    {
        return new Expr\Assign($this->assignExpr, $this->rootExpr);
    }

    public function getRootExpr(): Expr
    {
        return $this->rootExpr;
    }

    public function getSilentVariable(): ?Expr\Variable
    {
        return $this->silentVariable;
    }

    public function getReturnSilentVariable(): Return_
    {
        if ($this->silentVariable === null) {
            throw new ShouldNotHappenException();
        }

        return new Return_($this->silentVariable);
    }

    public function getCallerExpr(): Expr
    {
        if ($this->silentVariable !== null) {
            return $this->silentVariable;
        }

        return $this->assignExpr;
    }
}
