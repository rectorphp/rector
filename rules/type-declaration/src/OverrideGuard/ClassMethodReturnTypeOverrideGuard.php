<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\OverrideGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitor;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @var array<string, array<string>>
     */
    private const CHAOTIC_CLASS_METHOD_NAMES = [
        NodeVisitor::class => ['enterNode', 'leaveNode', 'beforeTraverse', 'afterTraverse'],
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // 1. skip magic methods
        if ($this->nodeNameResolver->isName($classMethod->name, '__*')) {
            return true;
        }

        // 2. skip chaotic contract class methods
        return $this->skipChaoticClassMethods($classMethod);
    }

    public function shouldSkipClassMethodOldTypeWithNewType(Type $oldType, Type $newType): bool
    {
        if ($oldType instanceof MixedType) {
            return false;
        }

        return $oldType->isSuperTypeOf($newType)->yes();
    }

    private function skipChaoticClassMethods(ClassMethod $classMethod): bool
    {
        /** @var string|null $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        foreach (self::CHAOTIC_CLASS_METHOD_NAMES as $chaoticClass => $chaoticMethodNames) {
            if (! is_a($className, $chaoticClass, true)) {
                continue;
            }

            return in_array($methodName, $chaoticMethodNames, true);
        }

        return false;
    }
}
