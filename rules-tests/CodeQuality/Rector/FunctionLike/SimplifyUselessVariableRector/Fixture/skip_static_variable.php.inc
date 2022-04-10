<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

class SkipStaticVariable
{
    public function run()
    {
        static $content;

        $content .= 'test';

        return $content;
    }
}
