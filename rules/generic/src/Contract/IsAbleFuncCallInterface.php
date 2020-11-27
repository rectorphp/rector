<?php

declare(strict_types=1);

namespace Rector\Generic\Contract;

interface IsAbleFuncCallInterface
{
    public function getFuncName(): string;

    public function getPhpVersion(): int;

    public function getType(): string;
}
