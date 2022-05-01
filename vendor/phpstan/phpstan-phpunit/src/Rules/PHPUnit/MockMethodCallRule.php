<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject;
use RectorPrefix20220501\PHPUnit\Framework\MockObject\Stub;
use function array_filter;
use function count;
use function implode;
use function in_array;
use function sprintf;
/**
 * @implements Rule<MethodCall>
 */
class MockMethodCallRule implements \PHPStan\Rules\Rule
{
    public function getNodeType() : string
    {
        return \PhpParser\Node\Expr\MethodCall::class;
    }
    public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : array
    {
        /** @var Node\Expr\MethodCall $node */
        $node = $node;
        if (!$node->name instanceof \PhpParser\Node\Identifier || $node->name->name !== 'method') {
            return [];
        }
        if (\count($node->getArgs()) < 1) {
            return [];
        }
        $argType = $scope->getType($node->getArgs()[0]->value);
        if (!$argType instanceof \PHPStan\Type\Constant\ConstantStringType) {
            return [];
        }
        $method = $argType->getValue();
        $type = $scope->getType($node->var);
        if ($type instanceof \PHPStan\Type\IntersectionType && (\in_array(\RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject::class, $type->getReferencedClasses(), \true) || \in_array(\RectorPrefix20220501\PHPUnit\Framework\MockObject\Stub::class, $type->getReferencedClasses(), \true)) && !$type->hasMethod($method)->yes()) {
            $mockClass = \array_filter($type->getReferencedClasses(), static function (string $class) : bool {
                return $class !== \RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject::class && $class !== \RectorPrefix20220501\PHPUnit\Framework\MockObject\Stub::class;
            });
            return [\sprintf('Trying to mock an undefined method %s() on class %s.', $method, \implode('&', $mockClass))];
        }
        if ($type instanceof \PHPStan\Type\Generic\GenericObjectType && $type->getClassName() === \RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker::class && \count($type->getTypes()) > 0) {
            $mockClass = $type->getTypes()[0];
            if ($mockClass instanceof \PHPStan\Type\ObjectType && !$mockClass->hasMethod($method)->yes()) {
                return [\sprintf('Trying to mock an undefined method %s() on class %s.', $method, $mockClass->getClassName())];
            }
        }
        return [];
    }
}
