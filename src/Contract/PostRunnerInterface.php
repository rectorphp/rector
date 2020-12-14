<?php

declare(strict_types=1);

namespace Rector\Core\Contract;

interface PostRunnerInterface
{
    public function run(): void;
}
