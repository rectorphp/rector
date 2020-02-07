<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\Fixture;

class KeepNested
{
    public function run(int $a)
    {
         if ($a > 5) {
             return $a;
         } else

         $a++;

         return $a;
    }
}
