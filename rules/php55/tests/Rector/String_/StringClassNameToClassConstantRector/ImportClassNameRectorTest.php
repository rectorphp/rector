<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

final class ImportClassNameRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);

        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureImport');
    }

    protected function getRectorClass(): string
    {
        return StringClassNameToClassConstantRector::class;
    }
}
