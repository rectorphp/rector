<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;
    }
}

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedParameterRector\Fixture;

    class KeepAnonymousClasses
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
