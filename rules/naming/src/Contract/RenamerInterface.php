<?php

declare(strict_types=1);

namespace Rector\Naming\Contract;

use PhpParser\Node;

interface RenamerInterface
{
    public function rename(RenameValueObjectInterface $renameValueObject): ?Node;
}
