<?php

declare(strict_types=1);

namespace Rector\Renaming\Contract;

interface MethodCallRenameInterface
{
    public function getOldClass(): string;

    public function getOldMethod(): string;

    public function getNewMethod(): string;
}
