<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\NodeFinder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNameResolver\NodeNameResolver;
final class PropertyGetterFinder
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function find(Property $property, Class_ $class): ?ClassMethod
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($class->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }
            if ($classMethod->isAbstract()) {
                continue;
            }
            if ($classMethod->stmts === null) {
                continue;
            }
            if (count($classMethod->stmts) !== 1) {
                continue;
            }
            $onlyStmt = $classMethod->stmts[0];
            if (!$onlyStmt instanceof Return_) {
                continue;
            }
            if (!$onlyStmt->expr instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $onlyStmt->expr;
            if (!$propertyFetch->var instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                continue;
            }
            return $classMethod;
        }
        return null;
    }
}
