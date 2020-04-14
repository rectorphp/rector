<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipProperty
{
    /**
     * @var string[]
     */
    private $days;

    public function run()
    {
        $this->days = [1 => 'one', 'two'];
    }

    public function useMe()
    {
        return $this->days;
    }
}
