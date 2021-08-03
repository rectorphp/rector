<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass;

final class SkipOtherClassHasConstructParameter
{
    public function run()
    {
        return [OtherClass::class, 'someMethod'];
    }
}
?>
