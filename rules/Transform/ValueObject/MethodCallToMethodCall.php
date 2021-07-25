<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class MethodCallToMethodCall
{
    /**
     * @param class-string $oldType
     * @param class-string $newType
     */
    public function __construct(
        private string $oldType,
        private string $oldMethod,
        private string $newType,
        private string $newMethod,
    ) {
    }

    public function getOldType(): string
    {
        return $this->oldType;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewType(): string
    {
        return $this->newType;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }
}
