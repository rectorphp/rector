<?php

namespace Rector\Tests\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

final class SkipOnFinalClass
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}
