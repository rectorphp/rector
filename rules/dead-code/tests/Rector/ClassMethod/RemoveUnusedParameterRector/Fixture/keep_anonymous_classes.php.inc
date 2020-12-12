<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;
    }
}

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

    class Baz
    {
        public function test()
        {
            $class = new class implements Foo
            {
                public function bar($value1)
                {
                }
            };
        }
    }
}
