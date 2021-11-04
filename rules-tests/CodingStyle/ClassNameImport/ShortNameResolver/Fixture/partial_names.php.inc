<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture;

use Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source;

final Class PartialNames
{
    public function run()
    {
        return new Source\SomeFile();
    }
}
