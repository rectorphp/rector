<?php

declare(strict_types=1);

namespace Rector\ElasticSearchDSL\Tests\Rector\MethodCall\MigrateFilterToQueryRector;

use Iterator;
use Rector\ElasticSearchDSL\Rector\MethodCall\MigrateFilterToQueryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MigrateFilterToQueryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MigrateFilterToQueryRector::class;
    }
}
