<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SkipNewline
{
    public function run()
    {
        return 'Hi' . "\n";
    }
}
