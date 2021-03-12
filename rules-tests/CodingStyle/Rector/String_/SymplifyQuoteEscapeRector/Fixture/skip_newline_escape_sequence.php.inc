<?php

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class SkipNewlineEscapeSequence
{
    public function run()
    {
        return 'Content-Type: multipart/related; boundary="'.$this->boundary."\"\n\t";
    }
}
