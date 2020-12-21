<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;

abstract class AbstractRootExpr implements RootExprAwareInterface, FirstCallFactoryAwareInterface
{
    /**
     * @var Expr
     */
    protected $assignExpr;

    public function createFirstAssign(): Assign
    {
        if ($this->isFirstCallFactory && $this->getFirstAssign() !== null) {
            return $this->createFactoryAssign();
        }

        return $this->createAssign($this->assignExpr, $this->rootExpr);
    }

    public function createAssign(Expr $assignVar, Expr $assignExpr): Assign
    {
        if ($assignVar === $assignExpr) {
            throw new ShouldNotHappenException();
        }

        return new Assign($assignVar, $assignExpr);
    }
}
