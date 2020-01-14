<?php

declare(strict_types=1);

namespace Rector\PHPStan\Reflection\Php;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;

final class ClosureInvokeMethodReflection implements MethodReflection
{
    /**
     * @var MethodReflection
     */
    private $nativeMethodReflection;

    /**
     * @var ClosureType
     */
    private $closureType;

    public function __construct(MethodReflection $nativeMethodReflection, ClosureType $closureType)
    {
        $this->nativeMethodReflection = $nativeMethodReflection;
        $this->closureType = $closureType;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->nativeMethodReflection->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->nativeMethodReflection->isStatic();
    }

    public function isPrivate(): bool
    {
        return $this->nativeMethodReflection->isPrivate();
    }

    public function isPublic(): bool
    {
        return $this->nativeMethodReflection->isPublic();
    }

    public function getDocComment(): ?string
    {
        return $this->nativeMethodReflection->getDocComment();
    }

    public function getName(): string
    {
        return $this->nativeMethodReflection->getName();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->nativeMethodReflection->getPrototype();
    }

    public function getVariants(): array
    {
        return [
            new FunctionVariant(
                $this->closureType->getTemplateTypeMap(),
                $this->closureType->getResolvedTemplateTypeMap(),
                $this->closureType->getParameters(),
                $this->closureType->isVariadic(),
                $this->closureType->getReturnType()
            ),
        ];
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->nativeMethodReflection->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->nativeMethodReflection->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->nativeMethodReflection->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->nativeMethodReflection->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->nativeMethodReflection->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->nativeMethodReflection->hasSideEffects();
    }
}
