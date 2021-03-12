<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         return $a;
         $a++;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         return $a;
    }
}

?>
