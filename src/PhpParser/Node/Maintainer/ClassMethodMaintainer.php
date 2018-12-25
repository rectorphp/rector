<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use function Safe\class_implements;

final class ClassMethodMaintainer
{
    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod): bool
    {
        $class = $classMethod->getAttribute(Attribute::CLASS_NAME);
        $method = $classMethod->getAttribute(Attribute::METHOD_NAME);

        if (! class_exists($class)) {
            return false;
        }

        $parentClass = $class;
        while ($parentClass = get_parent_class($parentClass)) {
            if (method_exists($parentClass, $method)) {
                return true;
            }
        }

        $implementedInterfaces = class_implements($class);
        foreach ($implementedInterfaces as $implementedInterface) {
            if (method_exists($implementedInterface, $method)) {
                return true;
            }
        }

        return false;
    }
}
