<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipError
{
    public function run()
    {
        return 'Error';

        return 'ERROR';
    }
}
