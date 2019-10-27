<?php declare(strict_types=1);

namespace Rector\Tests\Standalone\Source;

final class LowQualityFile
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump(! ($a === $b));
    }
}
