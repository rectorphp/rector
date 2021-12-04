<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\RenameClassConstFetchInterface;

final class RenameClassAndConstFetch implements RenameClassConstFetchInterface
{
    public function __construct(
        private readonly string $oldClass,
        private readonly string $oldConstant,
        private readonly string $newClass,
        private readonly string $newConstant
    ) {
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldClass);
    }

    public function getOldConstant(): string
    {
        return $this->oldConstant;
    }

    public function getNewConstant(): string
    {
        return $this->newConstant;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }
}
