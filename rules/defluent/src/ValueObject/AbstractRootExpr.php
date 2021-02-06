<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractRootExpr implements RootExprAwareInterface, FirstCallFactoryAwareInterface
{
    /**
     * @var bool
     */
    protected $isFirstCallFactory = false;

    /**
     * @var Expr
     */
    protected $rootExpr;

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

    protected function createAssign(Expr $assignVar, Expr $assignExpr): Assign
    {
        if ($assignVar === $assignExpr) {
            throw new ShouldNotHappenException();
        }

        return new Assign($assignVar, $assignExpr);
    }

    protected function getFirstAssign(): ?Assign
    {
        $currentStmt = $this->assignExpr->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if (! $currentStmt instanceof Expression) {
            return null;
        }

        if ($currentStmt->expr instanceof Assign) {
            return $currentStmt->expr;
        }

        return null;
    }

    private function createFactoryAssign(): Assign
    {
        /** @var Assign $firstAssign */
        $firstAssign = $this->getFirstAssign();
        $currentMethodCall = $firstAssign->expr;

        if (! $currentMethodCall instanceof MethodCall) {
            throw new ShouldNotHappenException();
        }

        $currentMethodCall = $this->resolveLastMethodCall($currentMethodCall);

        // ensure var and expr are different
        $assignVar = $firstAssign->var;
        $assignExpr = $currentMethodCall;

        return $this->createAssign($assignVar, $assignExpr);
    }

    private function resolveLastMethodCall(MethodCall $currentMethodCall): MethodCall
    {
        while ($currentMethodCall->var instanceof MethodCall) {
            $currentMethodCall = $currentMethodCall->var;
        }

        return $currentMethodCall;
    }
}
