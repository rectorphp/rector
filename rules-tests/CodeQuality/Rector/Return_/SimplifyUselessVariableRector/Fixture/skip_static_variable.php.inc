<?php

namespace Rector\Tests\CodeQuality\Rector\Return_\SimplifyUselessVariableRector\Fixture;

class SkipStaticVariable
{
    public function run()
    {
        static $content;

        $content .= 'test';

        return $content;
    }
}
