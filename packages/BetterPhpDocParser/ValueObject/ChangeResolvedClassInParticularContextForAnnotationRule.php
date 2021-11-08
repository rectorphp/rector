<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject;

final class ChangeResolvedClassInParticularContextForAnnotationRule
{
    public function __construct(
        private string $tag,
        private string $value,
        private string $resolvedClass
    ) {
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getResolvedClass(): string
    {
        return $this->resolvedClass;
    }
}
