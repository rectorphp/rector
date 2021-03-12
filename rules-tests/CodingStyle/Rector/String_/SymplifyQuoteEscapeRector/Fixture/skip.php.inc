<?php

namespace Rector\Tests\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class Skip
{
    public function run()
    {
         $name = "\" Tom'";
         $name = '\' Sara"';
    }
}
