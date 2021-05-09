<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\NodeAnalyzer;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
final class SetUpClassMethodUpdater
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function updateSetUpMethod(\PhpParser\Node\Stmt\ClassMethod $setupClassMethod, \PhpParser\Node\Stmt\Expression $parentSetupStaticCall, \PhpParser\Node\Stmt\Expression $assign) : void
    {
        $parentSetUpStaticCallPosition = $this->getParentSetUpStaticCallPosition($setupClassMethod);
        if ($parentSetUpStaticCallPosition === null) {
            $setupClassMethod->stmts = \array_merge([$parentSetupStaticCall, $assign], (array) $setupClassMethod->stmts);
        } else {
            \assert($setupClassMethod->stmts !== null);
            \array_splice($setupClassMethod->stmts, $parentSetUpStaticCallPosition + 1, 0, [$assign]);
        }
    }
    private function getParentSetUpStaticCallPosition(\PhpParser\Node\Stmt\ClassMethod $setupClassMethod) : ?int
    {
        foreach ((array) $setupClassMethod->stmts as $position => $methodStmt) {
            if ($methodStmt instanceof \PhpParser\Node\Stmt\Expression) {
                $methodStmt = $methodStmt->expr;
            }
            if (!$methodStmt instanceof \PhpParser\Node\Expr\StaticCall) {
                continue;
            }
            if (!$methodStmt->class instanceof \PhpParser\Node\Name) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($methodStmt->class, 'parent')) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($methodStmt->name, \Rector\Core\ValueObject\MethodName::SET_UP)) {
                continue;
            }
            return $position;
        }
        return null;
    }
}
