<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\NodeAnalyzer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class FreshArrayCollectionAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function doesReturnNewArrayCollectionVariable(ClassMethod $classMethod): bool
    {
        $newArrayCollectionVariableName = $this->resolveNewArrayCollectionVariableName($classMethod);
        if ($newArrayCollectionVariableName === null) {
            return \false;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if (count($returns) !== 1) {
            return \false;
        }
        // the exact variable name must be returned
        $soleReturn = $returns[0];
        if (!$soleReturn->expr instanceof Variable) {
            return \false;
        }
        return $this->nodeNameResolver->isName($soleReturn->expr, $newArrayCollectionVariableName);
    }
    private function resolveNewArrayCollectionVariableName(ClassMethod $classMethod): ?string
    {
        $assigns = $this->betterNodeFinder->findInstancesOfScoped([$classMethod], Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->expr instanceof New_) {
                continue;
            }
            $new = $assign->expr;
            if (!$this->nodeNameResolver->isName($new->class, DoctrineClass::ARRAY_COLLECTION)) {
                continue;
            }
            // detect variable name
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $variable = $assign->var;
            return $this->nodeNameResolver->getName($variable);
        }
        return null;
    }
}
