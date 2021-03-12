<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

use Nette\Utils\Strings;

class SkipConcat
{
    public function run($file)
    {
        Strings::replace($file, '#' . preg_quote(getcwd() . '/') . '#');
    }
}
