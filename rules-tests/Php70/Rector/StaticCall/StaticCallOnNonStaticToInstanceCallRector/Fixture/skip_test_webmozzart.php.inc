<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Webmozart\Assert\Assert;

final class SkipTestWebmozzart
{
    public function doWork($values)
    {
        Assert::allIsAOf('SomeType', $values);
    }
}
