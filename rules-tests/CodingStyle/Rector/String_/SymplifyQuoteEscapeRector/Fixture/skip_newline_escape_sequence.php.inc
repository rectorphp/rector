<?php

namespace Rector\Tests\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class SkipNewlineEscapeSequence
{
    public function run()
    {
        return 'Content-Type: multipart/related; boundary="'.$this->boundary."\"\n\t";
    }
}
