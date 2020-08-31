<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Function_\ReservedFnFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReservedFnFunctionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, array<string, array<string, string>>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReservedFnFunctionRector::class => [
                ReservedFnFunctionRector::RESERVED_NAMES_TO_NEW_ONES => [
                    // for testing purposes of "fn" even on PHP 7.3-
                    'reservedFn' => 'f',
                ],
            ],
        ];
    }
}
