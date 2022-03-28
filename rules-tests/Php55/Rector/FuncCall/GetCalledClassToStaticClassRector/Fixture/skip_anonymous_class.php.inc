<?php

namespace Rector\Tests\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector\Fixture;

new class {
    public function callOnMe()
    {
        var_dump( get_called_class() );
    }
};