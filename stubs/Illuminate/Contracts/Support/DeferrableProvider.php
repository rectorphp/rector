<?php

declare(strict_types=1);

namespace Illuminate\Contracts\Support;

if (interface_exists('Illuminate\Contracts\Support\DeferrableProvider')) {
    return;
}

interface DeferrableProvider
{
}
