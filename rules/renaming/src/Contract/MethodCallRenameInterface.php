<?php

declare(strict_types=1);

namespace Rector\Renaming\Contract;

use PHPStan\Type\ObjectType;

interface MethodCallRenameInterface
{
    public function getOldObjectType(): ObjectType;

    public function getOldMethod(): string;

    public function getNewMethod(): string;
}
