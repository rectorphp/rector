<?php

namespace Rector\Tests\Php55\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipErrorAndException
{
    public function run()
    {
        return 'Error' && 'Exception';

        return 'ERROR' && 'EXCEPTION';
    }
}
