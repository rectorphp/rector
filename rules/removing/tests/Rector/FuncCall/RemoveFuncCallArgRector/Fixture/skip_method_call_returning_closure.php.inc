<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

final class SkipMethodCallReturningClosure
{
    public function run()
    {
        $this->getClosure()('Hello');
    }
}

?>
