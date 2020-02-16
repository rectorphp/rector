<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue2863;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue2863Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config2863.yaml';
    }
}
