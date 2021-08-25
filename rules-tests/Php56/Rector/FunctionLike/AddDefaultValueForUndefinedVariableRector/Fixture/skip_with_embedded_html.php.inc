<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

final class SkipWithEmbeddedHTML
{
    public function run(): void
    {
        ?>
        <p>Let's insert stuff here.</p>
        <?php
        if (random_int(1, 10) === 5) {
            $a = true;
        }

        if (isset($a)) {
            doStuff();
        }
    }
}

?>
