<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodCall\MethodCallToReturnRector;
use Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MethodCallToReturnRectorTest extends AbstractRectorTestCase
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
            MethodCallToReturnRector::class => [
                MethodCallToReturnRector::METHOD_NAMES_BY_TYPE => [
                    ReturnDeny::class => ['deny'],
                ],
            ],
        ];
    }
}
