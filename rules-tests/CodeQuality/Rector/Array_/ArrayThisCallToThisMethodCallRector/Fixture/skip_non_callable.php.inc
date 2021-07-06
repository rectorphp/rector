<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Source\SomeConstantInteger;

final class SkipNonCallable
{
    public function run()
    {
        $this->process('tags', [
            'usage' => SomeConstantInteger::VALUE,
            'region' => 'tags',
        ]);
    }

    public function process()
    {
    }
}
