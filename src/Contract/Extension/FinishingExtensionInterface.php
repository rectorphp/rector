<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Extension;

interface FinishingExtensionInterface
{
    public function run(): void;
}
