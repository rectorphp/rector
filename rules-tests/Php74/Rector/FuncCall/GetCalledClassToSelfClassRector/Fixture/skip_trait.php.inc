<?php

namespace Rector\Tests\Php74\Rector\FuncCall\GetCalledClassToSelfClassRector\Fixture;

trait SkipTrait
{
    public function callOnMe()
    {
        var_dump( get_called_class());
    }
}
