<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\ValueObject;

use PHPStan\Type\Type;

final class OldToNewType
{
    public function __construct(
        private readonly Type $oldType,
        private readonly Type $newType
    ) {
    }

    public function getOldType(): Type
    {
        return $this->oldType;
    }

    public function getNewType(): Type
    {
        return $this->newType;
    }
}
