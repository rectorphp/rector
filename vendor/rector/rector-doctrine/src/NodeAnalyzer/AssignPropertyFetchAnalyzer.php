<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Doctrine\ValueObject\AssignToPropertyFetch;
use Rector\NodeNameResolver\NodeNameResolver;
final class AssignPropertyFetchAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return AssignToPropertyFetch[]
     */
    public function resolveAssignToPropertyFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Expr\Assign::class);
        $assignsToPropertyFetch = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            $propertyFetch = $assign->var;
            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyFetch);
            $assignsToPropertyFetch[] = new \Rector\Doctrine\ValueObject\AssignToPropertyFetch($assign, $propertyFetch, $propertyName);
        }
        return $assignsToPropertyFetch;
    }
}
