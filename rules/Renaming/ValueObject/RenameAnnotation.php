<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use Rector\Renaming\Contract\RenameAnnotationInterface;

/**
 * @api
 */
final class RenameAnnotation implements RenameAnnotationInterface
{
    public function __construct(
        private readonly string $oldAnnotation,
        private readonly string $newAnnotation
    ) {
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
