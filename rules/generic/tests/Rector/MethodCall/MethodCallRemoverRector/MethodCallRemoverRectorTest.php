<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodCall\MethodCallRemoverRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MethodCallRemoverRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MethodCallRemoverRector::class => [
                MethodCallRemoverRector::METHOD_CALL_REMOVER_ARGUMENT => [
                    'Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source\Car' => 'getCarType',
                ],
            ],
        ];
    }
}
