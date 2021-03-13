<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;

final class FirstAssignFluentCall extends AbstractRootExpr implements RootExprAwareInterface, FirstCallFactoryAwareInterface
{
    /**
     * @var FluentMethodCalls
     */
    private $fluentMethodCalls;

    public function __construct(
        Expr $assignExpr,
        Expr $rootExpr,
        bool $isFirstCallFactory,
        FluentMethodCalls $fluentMethodCalls
    ) {
        $this->assignExpr = $assignExpr;
        $this->rootExpr = $rootExpr;
        $this->isFirstCallFactory = $isFirstCallFactory;
        $this->fluentMethodCalls = $fluentMethodCalls;
    }

    public function getAssignExpr(): Expr
    {
        return $this->assignExpr;
    }

    public function getRootExpr(): Expr
    {
        return $this->rootExpr;
    }

    public function getCallerExpr(): Expr
    {
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
            return $this->assignExpr;
        }

        return $firstAssign->var;
    }

    /**
     * @return MethodCall[]
     */
    public function getFluentMethodCalls(): array
    {
        return $this->fluentMethodCalls->getFluentMethodCalls();
    }
}
