<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source;

final class ClassWithStaticProperties
{
    public static $value = 'yes';
}
