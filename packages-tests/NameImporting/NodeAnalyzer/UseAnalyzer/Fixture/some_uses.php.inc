<?php

declare(strict_types=1);

namespace Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\Fixture;

use Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\Source\FirstUsage;

final class SomeUses
{
    public function run()
    {
        return new FirstUsage();
    }
}
