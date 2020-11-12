<?php

namespace Carbon;

use DateInterval;
use DateTimeInterface;
use DateTimeZone;

if (class_exists('Carbon\Carbon')) {
    return;
}

class Carbon extends \DateTime
{
    public static function now(): self
    {
    }

    public static function today(): self
    {
    }
}
