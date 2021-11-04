<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture;

use Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source\SomeFile;

final Class VariousImports
{
    public function run()
    {
        return new SomeFile();
    }
}
