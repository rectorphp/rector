<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

final class SkipOnFinalClass
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}
