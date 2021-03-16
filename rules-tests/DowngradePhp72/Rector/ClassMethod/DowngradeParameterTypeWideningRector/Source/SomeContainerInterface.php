<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source;

interface SomeContainerInterface
{
    public function get(string $id);
}
