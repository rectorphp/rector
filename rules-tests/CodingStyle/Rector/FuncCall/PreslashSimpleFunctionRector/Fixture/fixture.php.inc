<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class Fixture
{
    public function shorten($value)
    {
        return trim($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class Fixture
{
    public function shorten($value)
    {
        return \trim($value);
    }
}

?>
