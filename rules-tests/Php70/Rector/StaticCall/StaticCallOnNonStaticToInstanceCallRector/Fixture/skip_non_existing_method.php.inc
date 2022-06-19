<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Source\ExistingClass;

final class SkipNonExistingMethod
{
    public function run()
    {
        return ExistingClass::someMethod();
    }
}
