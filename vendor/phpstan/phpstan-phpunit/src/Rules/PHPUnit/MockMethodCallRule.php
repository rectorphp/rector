<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use RectorPrefix20210510\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RectorPrefix20210510\PHPUnit\Framework\MockObject\MockObject;
/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Expr\MethodCall>
 */
class MockMethodCallRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return Node\Expr\MethodCall::class;
    }
    public function processNode(Node $node, Scope $scope) : array
    {
        /** @var Node\Expr\MethodCall $node */
        $node = $node;
        if (!$node->name instanceof Node\Identifier || $node->name->name !== 'method') {
            return [];
        }
        if (\count($node->args) < 1) {
            return [];
        }
        $argType = $scope->getType($node->args[0]->value);
        if (!$argType instanceof ConstantStringType) {
            return [];
        }
        $method = $argType->getValue();
        $type = $scope->getType($node->var);
        if ($type instanceof IntersectionType && \in_array(MockObject::class, $type->getReferencedClasses(), \true) && !$type->hasMethod($method)->yes()) {
            $mockClass = \array_filter($type->getReferencedClasses(), function (string $class) : bool {
                return $class !== MockObject::class;
            });
            return [\sprintf('Trying to mock an undefined method %s() on class %s.', $method, \implode('&', $mockClass))];
        }
        if ($type instanceof GenericObjectType && $type->getClassName() === InvocationMocker::class && \count($type->getTypes()) > 0) {
            $mockClass = $type->getTypes()[0];
            if ($mockClass instanceof ObjectType && !$mockClass->hasMethod($method)->yes()) {
                return [\sprintf('Trying to mock an undefined method %s() on class %s.', $method, $mockClass->getClassName())];
            }
        }
        return [];
    }
}
