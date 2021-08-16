<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\MixinClass;

class SkipMixinInSource
{
    public function someFunction()
    {
        $queryBuilder = new MixinClass();
        $queryBuilder->addQuery()
                        ->select();
    }

    public function someFunction2(MixinClass $mixinClass)
    {
        $mixinClass->addQuery()
                        ->select();
    }
}
