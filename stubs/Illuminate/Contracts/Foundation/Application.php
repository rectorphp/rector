<?php

declare(strict_types=1);

namespace Illuminate\Contracts\Foundation;

if (class_exists('Illuminate\Contracts\Foundation\Application')) {
    return;
}

final class Application
{
    public function tagged(string $tagName): iterable
    {
        return [];
    }
}
