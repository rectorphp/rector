<?php

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector\Fixture;

class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
    }
}

?>
