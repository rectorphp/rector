<?php

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

final class SkipPropertyAssign
{
    private $command;

    public function __construct(\Closure $command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        if (!$this->command instanceof \Closure) {
            return $this->command;
        }

        $this->command = ($this->command)();

        return $this->command;
    }
}
