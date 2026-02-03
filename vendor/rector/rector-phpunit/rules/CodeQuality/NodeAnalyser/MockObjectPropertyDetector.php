<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPUnit\Enum\PHPUnitClassName;
final class MockObjectPropertyDetector
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Property $property): bool
    {
        if (!$property->type instanceof FullyQualified) {
            return \false;
        }
        return $property->type->toString() === PHPUnitClassName::MOCK_OBJECT;
    }
    /**
     * @return array<string, MethodCall>
     */
    public function collectFromClassMethod(ClassMethod $classMethod): array
    {
        $propertyNamesToCreateMockMethodCalls = [];
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $assign->expr;
            if (!$this->nodeNameResolver->isName($methodCall->name, 'createMock')) {
                continue;
            }
            $propertyFetch = $assign->var;
            $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
            if (!is_string($propertyName)) {
                continue;
            }
            $propertyNamesToCreateMockMethodCalls[$propertyName] = $methodCall;
        }
        return $propertyNamesToCreateMockMethodCalls;
    }
}
