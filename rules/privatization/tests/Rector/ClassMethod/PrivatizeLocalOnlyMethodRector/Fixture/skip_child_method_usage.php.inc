<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

final class SkipChildMethodUsage extends AbstractWithTearDown
{
    protected function tearDown(): void
    {
        $value = 5;
    }
}

abstract class AbstractWithTearDown
{
    /**
     * @api
     */
    public function run()
    {
        $this->tearDown();
    }

    protected function tearDown(): void
    {
    }
}
