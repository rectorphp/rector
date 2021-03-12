<?php

namespace Rector\Tests\Php55\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class SkipNonString
{
    public function run($contents)
    {
        $contents = preg_replace(['/\s+$/Sm', '/\n+/S'], "\n", $contents);
    }
}
