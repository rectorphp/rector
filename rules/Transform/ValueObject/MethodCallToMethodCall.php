<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use Rector\Core\Validation\RectorAssert;

final class MethodCallToMethodCall
{
    /**
     * @param class-string $oldType
     * @param class-string $newType
     */
    public function __construct(
        private readonly string $oldType,
        private readonly string $oldMethod,
        private readonly string $newType,
        private readonly string $newMethod,
    ) {
        RectorAssert::className($oldType);
        RectorAssert::className($newType);
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
