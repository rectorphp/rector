<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

use Nette\Utils\Strings;

class SkipNewLine
{
    public function run()
    {
        $content = 'some texte';
        $parts = preg_split("/(\r\n|\n|\r){2}/", $content);
    }
}
