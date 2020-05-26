<?php

declare(strict_types=1);

namespace Rector\Extension\Contract;

interface FinishingExtensionInterface
{
    public function run(): void;
}
