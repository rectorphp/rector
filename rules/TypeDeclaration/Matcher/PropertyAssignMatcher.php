<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Matcher;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class PropertyAssignMatcher
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * Covers:
     * - $this->propertyName = $expr;
     * - $this->propertyName[] = $expr;
     */
    public function matchPropertyAssignExpr(\PhpParser\Node\Expr\Assign $assign, string $propertyName) : ?\PhpParser\Node\Expr
    {
        if ($this->propertyFetchAnalyzer->isPropertyFetch($assign->var)) {
            if (!$this->nodeNameResolver->isName($assign->var, $propertyName)) {
                return null;
            }
            return $assign->expr;
        }
        if ($assign->var instanceof \PhpParser\Node\Expr\ArrayDimFetch && $this->propertyFetchAnalyzer->isPropertyFetch($assign->var->var)) {
            if (!$this->nodeNameResolver->isName($assign->var->var, $propertyName)) {
                return null;
            }
            return $assign->expr;
        }
        return null;
    }
}
