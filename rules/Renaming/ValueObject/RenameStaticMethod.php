<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;

final class RenameStaticMethod
{
    public function __construct(
        private string $oldClass,
        private string $oldMethod,
        private string $newClass,
        private string $newMethod
    ) {
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldClass);
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }

    public function hasClassChanged(): bool
    {
        return $this->oldClass !== $this->newClass;
    }
}
