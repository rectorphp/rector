<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipSlashes
{
    public function run()
    {
        $class = "\\";

        return '\\';
    }
}
