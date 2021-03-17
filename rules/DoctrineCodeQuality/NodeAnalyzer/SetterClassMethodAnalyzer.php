<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SetterClassMethodAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
    }

    public function matchNullalbeClassMethodProperty(ClassMethod $classMethod): ?Property
    {
        $propertyFetch = $this->matchNullalbeClassMethodPropertyFetch($classMethod);
        if (! $propertyFetch instanceof PropertyFetch) {
            return null;
        }

        return $this->nodeRepository->findPropertyByPropertyFetch($propertyFetch);
    }

    public function matchDateTimeSetterProperty(ClassMethod $classMethod): ?Property
    {
        $propertyFetch = $this->matchDateTimeSetterPropertyFetch($classMethod);
        if (! $propertyFetch instanceof PropertyFetch) {
            return null;
        }

        return $this->nodeRepository->findPropertyByPropertyFetch($propertyFetch);
    }

    /**
     * Matches:
     *
     * public function setSomething(?Type $someValue);
     * {
     *      <$this->someProperty> = $someValue;
     * }
     */
    private function matchNullalbeClassMethodPropertyFetch(ClassMethod $classMethod): ?PropertyFetch
    {
        $propertyFetch = $this->matchSetterOnlyPropertyFetch($classMethod);
        if (! $propertyFetch instanceof PropertyFetch) {
            return null;
        }

        // is nullable param
        $onlyParam = $classMethod->params[0];
        if (! $this->nodeTypeResolver->isNullableObjectType($onlyParam)) {
            return null;
        }

        return $propertyFetch;
    }

    private function matchDateTimeSetterPropertyFetch(ClassMethod $classMethod): ?PropertyFetch
    {
        $propertyFetch = $this->matchSetterOnlyPropertyFetch($classMethod);
        if (! $propertyFetch instanceof PropertyFetch) {
            return null;
        }

        $param = $classMethod->params[0];
        $paramType = $this->nodeTypeResolver->getStaticType($param);

        if (! $paramType instanceof TypeWithClassName) {
            return null;
        }

        if ($paramType->getClassName() !== 'DateTimeInterface') {
            return null;
        }

        return $propertyFetch;
    }

    private function matchSetterOnlyPropertyFetch(ClassMethod $classMethod): ?PropertyFetch
    {
        if (count($classMethod->params) !== 1) {
            return null;
        }

        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $stmts[0] ?? null;
        if (! $onlyStmt instanceof Stmt) {
            return null;
        }

        if ($onlyStmt instanceof Expression) {
            $onlyStmt = $onlyStmt->expr;
        }

        if (! $onlyStmt instanceof Assign) {
            return null;
        }

        if (! $onlyStmt->var instanceof PropertyFetch) {
            return null;
        }

        $propertyFetch = $onlyStmt->var;
        if (! $this->isVariableName($propertyFetch->var, 'this')) {
            return null;
        }

        return $propertyFetch;
    }

    private function isVariableName(?Node $node, string $name): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->nodeNameResolver->isName($node, $name);
    }
}
