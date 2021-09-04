<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source\OtherClass3;

final class OtherClassConstruct
{
    public function run()
    {
        return [OtherClass3::class, '__construct'];
    }
}

?>
