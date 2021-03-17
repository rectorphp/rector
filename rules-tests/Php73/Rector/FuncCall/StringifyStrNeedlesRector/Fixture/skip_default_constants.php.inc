<?php

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

final class SkipDefaultConstants
{
    public function run()
    {
        return strstr("test", PHP_EOL);
    }
}
?>
