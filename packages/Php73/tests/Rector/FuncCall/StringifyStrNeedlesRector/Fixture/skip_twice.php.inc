<?php

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

class SkipTwice
{
    public function run()
    {
        if (false !== strpos($log->getText(), (string) $search->getQ())) {
        }
    }
}
