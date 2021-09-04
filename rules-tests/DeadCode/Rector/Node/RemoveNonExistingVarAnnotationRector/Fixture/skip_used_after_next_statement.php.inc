<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class SkipUsedAfterNextStatement
{
    public function get(): void
    {
        $options = [
            'a' => 'value a',
            'b' => 'value b',
            'c' => 'value c',
        ];

        extract($options);
        /**
         * @var string $a
         * @var string $b
         * @var string $c
         */
        $callback = function ($var1, $var2, $var3) {
            return $var1 . $var2 . $var3;
        };

        $callback($a, $b, $c);
    }
}

?>
