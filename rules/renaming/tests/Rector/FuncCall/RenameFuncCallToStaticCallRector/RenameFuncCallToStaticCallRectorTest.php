<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\FuncCall\RenameFuncCallToStaticCallRector;

final class RenameFuncCallToStaticCallRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameFuncCallToStaticCallRector::class => [
                '$functionsToStaticCalls' => [
                    'strPee' => ['Strings', 'strPaa'],
                ],
            ],
        ];
    }
}
