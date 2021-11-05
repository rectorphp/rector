<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Node\UseManipulator\Fixture;

use Rector\Tests\CodingStyle\Node\UseManipulator\Source\FirstUsage;

final class SomeUses
{
    public function run()
    {
        return new FirstUsage();
    }
}
