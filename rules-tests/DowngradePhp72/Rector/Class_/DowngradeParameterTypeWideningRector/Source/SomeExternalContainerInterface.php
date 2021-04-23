<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source;

interface SomeExternalContainerInterface
{
    public function get(string $id);
}
