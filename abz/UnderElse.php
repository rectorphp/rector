<?php declare(strict_types=1);


final class UnderElse
{
    public function run($value)
    {
        if ($value) {
            return 5;
        } else {
            return 10;
        }
    }
}
