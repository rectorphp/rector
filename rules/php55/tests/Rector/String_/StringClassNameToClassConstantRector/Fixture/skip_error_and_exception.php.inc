<?php

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipErrorAndException
{
    public function run()
    {
        return 'Error' && 'Exception';

        return 'ERROR' && 'EXCEPTION';
    }
}
