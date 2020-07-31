<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
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
     * @var Variable|null
     */
    private $silentVariable;

    public function __construct(Expr $assignExpr, Expr $rootExpr, ?Variable $silentVariable = null)
    {
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
        $this->silentVariable = $silentVariable;
    }

    public function getAssignExpr(): Expr
    {
        return $this->assignExpr;
    }

    public function getRootExpr(): Expr
    {
        return $this->rootExpr;
    }

    public function getSilentVariable(): ?Variable
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

    public function getFirstAssign(): Assign
    {
        return new Assign($this->assignExpr, $this->rootExpr);
    }

    public function getCallerExpr(): Expr
    {
        if ($this->silentVariable !== null) {
            return $this->silentVariable;
        }

        return $this->assignExpr;
    }
}
