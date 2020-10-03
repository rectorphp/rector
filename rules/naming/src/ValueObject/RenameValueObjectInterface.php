<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

interface RenameValueObjectInterface
{
    public function getCurrentName(): string;

    public function getExpectedName(): string;
}
