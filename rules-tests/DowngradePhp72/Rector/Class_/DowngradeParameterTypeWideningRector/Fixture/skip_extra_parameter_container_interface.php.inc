<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Psr\Container\ContainerInterface;

interface SkipExtraParameterContainerInterface extends ContainerInterface
{
    public function get(string $id, int $invalidBehavior = 100);

    public function has(string $id);
}
