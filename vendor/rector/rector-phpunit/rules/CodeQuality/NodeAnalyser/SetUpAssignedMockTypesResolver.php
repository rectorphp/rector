<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ValueObject\MethodName;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class SetUpAssignedMockTypesResolver
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
     * @return array<string, string>
     */
    public function resolveFromClass(Class_ $class) : array
    {
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return [];
        }
        $propertyNameToMockedTypes = [];
        foreach ((array) $setUpClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($assign->expr->name, ['createMock', 'getMockBuilder'])) {
                continue;
            }
            if (!$assign->var instanceof PropertyFetch && !$assign->var instanceof Variable) {
                continue;
            }
            $mockedClassNameExpr = $assign->expr->getArgs()[0]->value;
            if (!$mockedClassNameExpr instanceof ClassConstFetch) {
                continue;
            }
            $propertyOrVariableName = $this->resolvePropertyOrVariableName($assign->var);
            $mockedClass = $this->nodeNameResolver->getName($mockedClassNameExpr->class);
            Assert::string($mockedClass);
            $propertyNameToMockedTypes[$propertyOrVariableName] = $mockedClass;
        }
        return $propertyNameToMockedTypes;
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable $propertyFetchOrVariable
     */
    private function resolvePropertyOrVariableName($propertyFetchOrVariable) : ?string
    {
        if ($propertyFetchOrVariable instanceof Variable) {
            return $this->nodeNameResolver->getName($propertyFetchOrVariable);
        }
        return $this->nodeNameResolver->getName($propertyFetchOrVariable->name);
    }
}
