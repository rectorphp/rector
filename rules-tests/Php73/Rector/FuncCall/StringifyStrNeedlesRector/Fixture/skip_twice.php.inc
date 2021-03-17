<?php

namespace Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

class SkipTwice
{
    public function run()
    {
        if (false !== strpos($log->getText(), (string) $search->getQ())) {
        }
    }
}
