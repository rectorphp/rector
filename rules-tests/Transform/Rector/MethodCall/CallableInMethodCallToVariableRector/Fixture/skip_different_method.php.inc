<?php

namespace Rector\Tests\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;

final class SkipDifferentMethod
{
    public function run(DummyCache $dummyCache, $someValue)
    {
        $dummyCache->load('key', function ($someValue) {
            $someValue= $someValue + 1000;
            return $someValue + 10;
        });
    }
}
