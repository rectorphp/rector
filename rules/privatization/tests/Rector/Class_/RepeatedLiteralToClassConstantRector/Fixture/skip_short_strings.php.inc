<?php

namespace Rector\Privatization\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

use Nette\Utils\Strings;

class SkipShortStrings
{
    public function run($key, $items)
    {
        $inlined = Strings::replace($items, '#\s+#', ' ');
        $inlined = Strings::replace($items, '#\s+#', ' ');
        $inlined = Strings::replace($items, '#\s+#', ' ');
    }
}
