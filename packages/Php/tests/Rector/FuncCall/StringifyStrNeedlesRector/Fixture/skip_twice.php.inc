<?php

namespace Rector\Php\Tests\Rector\FuncCall\StringifyStrNeedlesRector\Fixture;

class SkipTwice
{
    public function run()
    {
        if (false !== strpos($log->getText(), (string) $search->getQ())) {
        }
    }
}
