<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run(int $a)
    {
         return $a;
    }
}

?>
