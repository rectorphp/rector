<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class VendorFileDetector
{
    /**
     * @see https://regex101.com/r/ChpDsj/1
     * @var string
     */
    private const ANONYMOUS_CLASS_REGEX = '#^AnonymousClass[\w+]#';

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function isNodeInVendor(Node $node): bool
    {
        $fileName = $node instanceof ClassMethod
            ? $this->getClassFileNameByClassMethod($node)
            : $this->getClassFileNameByMethodCall($node);

        if ($fileName === null) {
            return false;
        }

        return Strings::contains($fileName, 'vendor');
    }

    private function getClassFileNameByClassMethod(ClassMethod $classMethod): ?string
    {
        $parent = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Class_) {
            return null;
        }

        $shortClassName = (string) $parent->name;
        if (Strings::match($shortClassName, self::ANONYMOUS_CLASS_REGEX)) {
            return null;
        }

        $reflectionClass = new ReflectionClass((string) $parent->namespacedName);
        return (string) $reflectionClass->getFileName();
    }

    private function getClassFileNameByMethodCall(MethodCall $methodCall): ?string
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($methodCall->var);
        if ($type instanceof ObjectType) {
            $classReflection = $type->getClassReflection();
            if ($classReflection === null) {
                return null;
            }

            return (string) $classReflection->getFileName();
        }

        if ($type instanceof ThisType) {
            $staticObjectType = $type->getStaticObjectType();
            $classReflection = $staticObjectType->getClassReflection();
            if ($classReflection === null) {
                return null;
            }

            return (string) $classReflection->getFileName();
        }

        return null;
    }
}
