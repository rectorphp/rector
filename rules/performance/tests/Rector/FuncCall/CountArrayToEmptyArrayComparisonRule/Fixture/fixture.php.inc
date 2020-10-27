<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SomeClass
{
    public function shorten(array $array)
    {
        count($array) === 0;
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SomeClass
{
    public function shorten(array $array)
    {
        $array === [];
    }
}

?>
