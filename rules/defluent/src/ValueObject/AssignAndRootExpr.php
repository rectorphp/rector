<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\Contract\ValueObject\RootExprAwareInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AssignAndRootExpr implements RootExprAwareInterface, FirstCallFactoryAwareInterface
{
    /**
     * @var bool
     */
    private $isFirstCallFactory = false;

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
        if ($this->silentVariable === null) {
            throw new ShouldNotHappenException();
        }

        return new Return_($this->silentVariable);
    }

    public function createFirstAssign(): Assign
    {
        if ($this->isFirstCallFactory && $this->getFirstAssign() !== null) {
            return $this->createFactoryAssign();
        }

        return $this->createAssign($this->assignExpr, $this->rootExpr);
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
        if ($firstAssign === null) {
            return $this->getCallerExpr();
        }

        return $firstAssign->var;
    }

    private function getFirstAssign(): ?Assign
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

    private function createAssign(Expr $assignVar, Expr $assignExpr): Assign
    {
        if ($assignVar === $assignExpr) {
            throw new ShouldNotHappenException();
        }

        return new Assign($assignVar, $assignExpr);
    }

    private function resolveLastMethodCall(MethodCall $currentMethodCall): MethodCall
    {
        while ($currentMethodCall->var instanceof MethodCall) {
            $currentMethodCall = $currentMethodCall->var;
        }

        return $currentMethodCall;
    }
}
