<?php

declare(strict_types=1);

namespace App;

final class ScreenSample
{
    public function run($value)
    {
        $value = is_string($value) ? 5 : $value;

        return $value;
    }
}
