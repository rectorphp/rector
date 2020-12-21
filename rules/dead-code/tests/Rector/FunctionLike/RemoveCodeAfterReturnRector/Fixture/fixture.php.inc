<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class Fixture
{
    public function run(int $a)
    {
         return $a;
    }
}

?>
