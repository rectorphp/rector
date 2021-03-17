<?php

namespace Rector\Tests\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Webmozart\Assert\Assert;

final class SkipTestWebmozzart
{
    public function doWork($values)
    {
        Assert::allIsAOf('SomeType', $values);
    }
}
