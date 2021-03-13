<?php

declare(strict_types=1);

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Source;

final class AnotherClass
{
    public static function eat()
    {
    }
}
