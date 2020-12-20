<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassMethodAndPropertyAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function hasClassMethodOnlyStatementReturnOfPropertyFetch(
        ClassMethod $classMethod,
        string $propertyName
    ): bool {
        if (count((array) $classMethod->stmts) !== 1) {
            return false;
        }

        $onlyClassMethodStmt = $classMethod->stmts[0];
        if (! $onlyClassMethodStmt instanceof Return_) {
            return false;
        }

        /** @var Return_ $return */
        $return = $onlyClassMethodStmt;

        if (! $return->expr instanceof PropertyFetch) {
            return false;
        }

        return $this->nodeNameResolver->isName($return->expr, $propertyName);
    }
}
