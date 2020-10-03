<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use Rector\Naming\ValueObject\RenameValueObjectInterface;

interface RenamerInterface
{
    public function rename(RenameValueObjectInterface $renameValueObject): ?Node;
}
