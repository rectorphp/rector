<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class SkipUsedAfterNextStatement2
{
    public function get(): void
    {
        /**
         * @var string $a
         * @var string $b
         * @var string $c
         */
        $a = run();
        $b = run2();
        $c = run3();
        $callback = function ($var1, $var2, $var3) {
            return $var1 . $var2 . $var3;
        };

        $callback($a, $b, $c);
    }
}

?>
