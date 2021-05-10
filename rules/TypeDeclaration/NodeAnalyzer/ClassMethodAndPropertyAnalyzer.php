<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassMethodAndPropertyAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasClassMethodOnlyStatementReturnOfPropertyFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : bool
    {
        $stmts = (array) $classMethod->stmts;
        if (\count($stmts) !== 1) {
            return \false;
        }
        $onlyClassMethodStmt = $stmts[0] ?? null;
        if (!$onlyClassMethodStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        /** @var Return_ $return */
        $return = $onlyClassMethodStmt;
        if (!$return->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        return $this->nodeNameResolver->isName($return->expr, $propertyName);
    }
}
