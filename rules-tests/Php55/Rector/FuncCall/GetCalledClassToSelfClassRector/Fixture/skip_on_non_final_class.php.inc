<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector\Fixture;

class SkipOnNonFinalClass
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}
