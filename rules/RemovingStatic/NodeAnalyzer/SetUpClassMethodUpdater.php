<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\NodeAnalyzer;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;

final class SetUpClassMethodUpdater
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function updateSetUpMethod(
        ClassMethod $setupClassMethod,
        Expression $parentSetupStaticCall,
        Expression $assign
    ): void {
        $parentSetUpStaticCallPosition = $this->getParentSetUpStaticCallPosition($setupClassMethod);
        if ($parentSetUpStaticCallPosition === null) {
            $setupClassMethod->stmts = array_merge([$parentSetupStaticCall, $assign], (array) $setupClassMethod->stmts);
        } else {
            assert($setupClassMethod->stmts !== null);
            array_splice($setupClassMethod->stmts, $parentSetUpStaticCallPosition + 1, 0, [$assign]);
        }
    }

    private function getParentSetUpStaticCallPosition(ClassMethod $setupClassMethod): ?int
    {
        foreach ((array) $setupClassMethod->stmts as $position => $methodStmt) {
            if ($methodStmt instanceof Expression) {
                $methodStmt = $methodStmt->expr;
            }

            if (! $methodStmt instanceof StaticCall) {
                continue;
            }

            if (! $methodStmt->class instanceof Name) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($methodStmt->class, 'parent')) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($methodStmt->name, MethodName::SET_UP)) {
                continue;
            }

            return $position;
        }

        return null;
    }
}
