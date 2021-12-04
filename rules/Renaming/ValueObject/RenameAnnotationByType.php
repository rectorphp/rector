<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
use Rector\Renaming\Contract\RenameAnnotationInterface;

final class RenameAnnotationByType implements RenameAnnotationInterface
{
    public function __construct(
        private readonly string $type,
        private readonly string $oldAnnotation,
        private readonly string $newAnnotation
    ) {
        RectorAssert::className($type);
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
