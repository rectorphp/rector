<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipError
{
    public function run()
    {
        return 'Error';

        return 'ERROR';
    }
}
