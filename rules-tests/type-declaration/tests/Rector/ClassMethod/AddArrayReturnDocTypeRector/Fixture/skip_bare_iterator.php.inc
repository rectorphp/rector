<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;

final class SkipBareIterator
{
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    private function yieldFilesFromDirectory(string $string): Iterator
    {
    }
}
