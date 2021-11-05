<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Source\ContainerInterface;
use Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\Source\FooInterface;

interface SkipExtendNoRelation extends ContainerInterface, FooInterface
{
}
