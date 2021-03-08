<?php

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

use Webmozart\Assert\Assert;

final class TestWebmozzart
{
    public function doWork($values)
    {
        Assert::allIsAOf(TestWebmozzart::class, $values);
    }
}
