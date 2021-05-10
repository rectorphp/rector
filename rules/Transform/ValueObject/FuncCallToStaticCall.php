<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class FuncCallToStaticCall
{
    public function __construct(
        private string $oldFuncName,
        private string $newClassName,
        private string $newMethodName
    ) {
    }

    public function getOldFuncName(): string
    {
        return $this->oldFuncName;
    }

    public function getNewClassName(): string
    {
        return $this->newClassName;
    }

    public function getNewMethodName(): string
    {
        return $this->newMethodName;
    }
}
