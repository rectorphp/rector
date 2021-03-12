<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

final class SkipConditionBefore
{
    /**
     * @var bool
     */
    private $shouldUpdate = false;

    public function run()
    {
        $this->shouldUpdate = false;

        $this->someMethod();
        if ($this->shouldUpdate === false) {
            return 5;
        }

        $value = 5;
        $value2 = 1515;
    }

    private function someMethod(): void
    {
        $this->shouldUpdate = true;
    }
}
