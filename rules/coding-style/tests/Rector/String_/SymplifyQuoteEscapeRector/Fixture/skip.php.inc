<?php

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class Skip
{
    public function run()
    {
         $name = "\" Tom'";
         $name = '\' Sara"';
    }
}
