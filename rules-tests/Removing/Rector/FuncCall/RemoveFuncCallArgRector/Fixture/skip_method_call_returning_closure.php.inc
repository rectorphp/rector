<?php

namespace Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallArgRector\Fixture;

final class SkipMethodCallReturningClosure
{
    public function run()
    {
        $this->getClosure()('Hello');
    }
}

?>
