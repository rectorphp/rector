<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;

final class RenameAnnotation
{
    public function __construct(
        private string $type,
        private string $oldAnnotation,
        private string $newAnnotation
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getOldAnnotation(): string
    {
        return $this->oldAnnotation;
    }

    public function getNewAnnotation(): string
    {
        return $this->newAnnotation;
    }
}
