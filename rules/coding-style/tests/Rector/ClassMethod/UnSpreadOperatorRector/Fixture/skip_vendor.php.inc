<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class SkipVendor
{
    public function run(...$args)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}

?>