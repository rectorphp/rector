<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Extension;

interface ReportingExtensionInterface
{
    public function run(): void;
}
