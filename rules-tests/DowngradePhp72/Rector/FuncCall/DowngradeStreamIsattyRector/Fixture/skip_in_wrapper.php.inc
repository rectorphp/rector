<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

final class SkipInWrapper
{
    public function run($stream)
    {
        if (function_exists('stream_isatty')) {
            return stream_isatty($stream);
        }

        return null;
    }
}
