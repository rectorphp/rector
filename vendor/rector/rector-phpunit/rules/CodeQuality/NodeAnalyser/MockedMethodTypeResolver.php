<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeAnalyser;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Enum\ClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPUnit\CodeQuality\ValueObject\MockedMethod;
use Rector\PHPUnit\Enum\PHPUnitClassName;
final class MockedMethodTypeResolver
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Resolves the mocked ->method('name') caller intersection type and method name, or null when it is not a mock.
     *
     * @param array<string, string> $propertyNameToMockedTypes
     */
    public function resolve(MethodCall $methodMethodCall, array $propertyNameToMockedTypes): ?MockedMethod
    {
        if (!$this->nodeNameResolver->isName($methodMethodCall->name, 'method')) {
            return null;
        }
        $methodNameExpr = $methodMethodCall->getArgs()[0]->value;
        if (!$methodNameExpr instanceof String_) {
            return null;
        }
        $methodName = $methodNameExpr->value;
        $callerType = $this->nodeTypeResolver->getType($methodMethodCall->var);
        $callerExpr = $methodMethodCall;
        if ($callerType instanceof ObjectType && in_array($callerType->getClassName(), [PHPUnitClassName::INVOCATION_MOCKER, PHPUnitClassName::INVOCATION_MOCKER_INTERFACE, PHPUnitClassName::INVOCATION_STUBBER], \true)) {
            $callerExpr = $methodMethodCall->var;
            if ($callerExpr instanceof MethodCall) {
                $callerType = $this->nodeTypeResolver->getType($callerExpr->var);
            }
        }
        $callerType = $this->fallbackMockedObjectInSetUp($callerType, $callerExpr, $propertyNameToMockedTypes);
        if (!$callerType instanceof IntersectionType) {
            return null;
        }
        return new MockedMethod($methodName, $callerType);
    }
    /**
     * @param array<string, string> $propertyNameToMockedTypes
     */
    private function fallbackMockedObjectInSetUp(Type $callerType, Expr $expr, array $propertyNameToMockedTypes): Type
    {
        if (!$callerType instanceof ObjectType && !$callerType instanceof NeverType) {
            return $callerType;
        }
        if (!$expr instanceof MethodCall) {
            return $callerType;
        }
        if ($callerType instanceof ObjectType && $callerType->getClassName() !== ClassName::MOCK_OBJECT) {
            return $callerType;
        }
        // type is missing, because of "final" keyword on mocked class
        // resolve from setUp assignment instead
        if (!$expr->var instanceof PropertyFetch && !$expr->var instanceof Variable) {
            return $callerType;
        }
        if ($expr->var instanceof Variable) {
            $propertyOrVariableName = $this->nodeNameResolver->getName($expr->var);
        } else {
            $propertyOrVariableName = $this->nodeNameResolver->getName($expr->var->name);
        }
        if ($propertyOrVariableName !== null && isset($propertyNameToMockedTypes[$propertyOrVariableName])) {
            $mockedType = $propertyNameToMockedTypes[$propertyOrVariableName];
            return new IntersectionType([$callerType, new ObjectType($mockedType)]);
        }
        return $callerType;
    }
}
