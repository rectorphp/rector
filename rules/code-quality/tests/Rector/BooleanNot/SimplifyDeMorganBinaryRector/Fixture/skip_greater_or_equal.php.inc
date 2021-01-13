<?php

class SkipGreaterOrEqual
{
    function demo(int $a, int $b, int $c): bool
    {
        if (! ($c >= $a + $b)) {
            return true;
        }

        return false;
    }
}

?>
