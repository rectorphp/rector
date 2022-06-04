<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class SkipAnonymousClassConstructor
{
    public function start()
    {
        $this->run(5);
        $this->run();
    }

    private function run($value = 100)
    {
        $class = new class($value) {
            private $value;

            public function __construct($value)
            {
                $this->value = $value;
            }
        };
    }
}
