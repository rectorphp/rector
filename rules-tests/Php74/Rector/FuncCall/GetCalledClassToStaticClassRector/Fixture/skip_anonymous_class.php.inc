<?php

namespace Rector\Tests\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

new class {
    public function callOnMe()
    {
        var_dump( get_called_class() );
    }
};