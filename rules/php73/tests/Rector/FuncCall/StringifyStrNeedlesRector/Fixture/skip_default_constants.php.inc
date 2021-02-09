<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

final class SkipDefaultConstants
{
    public function run()
    {
        return strstr("test", PHP_EOL);
    }
}
?>
