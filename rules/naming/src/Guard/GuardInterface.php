<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use Rector\Naming\ValueObject\RenameValueObjectInterface;

interface GuardInterface
{
    public function check(RenameValueObjectInterface $renameValueObject): bool;
}
