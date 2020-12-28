<?php

declare(strict_types=1);

namespace Rector\Naming\Contract\Guard;

use Rector\Naming\Contract\RenameValueObjectInterface;

interface ConflictingGuardInterface
{
    public function check(RenameValueObjectInterface $renameValueObject): bool;
}
