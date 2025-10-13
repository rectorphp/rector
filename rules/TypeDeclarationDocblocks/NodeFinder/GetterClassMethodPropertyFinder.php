<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\NodeFinder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ValueObject\MethodName;
final class GetterClassMethodPropertyFinder
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    public function find(ClassMethod $classMethod, Class_ $class)
    {
        // we need exactly one statement of return
        if ($classMethod->stmts === null || count($classMethod->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $classMethod->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        if (!$onlyStmt->expr instanceof PropertyFetch) {
            return null;
        }
        $propertyFetch = $onlyStmt->expr;
        if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (!is_string($propertyName)) {
            return null;
        }
        $property = $class->getProperty($propertyName);
        if ($property instanceof Property) {
            return $property;
        }
        // try also promoted property in constructor
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        foreach ($constructClassMethod->getParams() as $param) {
            if (!$param->isPromoted()) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($param, $propertyName)) {
                continue;
            }
            return $param;
        }
        return null;
    }
}
