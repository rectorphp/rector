<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;

final class AssignAndRootExpr extends AbstractRootExpr implements RootExprAwareInterface, FirstCallFactoryAwareInterface
{
    /**
     * @var Variable|null
     */
    private $silentVariable;

    public function __construct(
        Expr $assignExpr,
        Expr $rootExpr,
        ?Variable $silentVariable = null,
        bool $isFirstCallFactory = false
    ) {
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
        $this->silentVariable = $silentVariable;
        $this->isFirstCallFactory = $isFirstCallFactory;
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
        if (! $this->silentVariable instanceof Variable) {
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

    public function isFirstCallFactory(): bool
    {
        return $this->isFirstCallFactory;
    }

    public function getFactoryAssignVariable(): Expr
    {
        $firstAssign = $this->getFirstAssign();
        if (! $firstAssign instanceof Assign) {
            return $this->getCallerExpr();
        }

        return $firstAssign->var;
    }
}
