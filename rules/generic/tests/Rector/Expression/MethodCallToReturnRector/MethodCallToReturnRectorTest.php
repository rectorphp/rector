<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector;

use Iterator;
use Rector\Generic\Rector\Expression\MethodCallToReturnRector;
use Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;
use Rector\Generic\ValueObject\MethodCallToReturn;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MethodCallToReturnRector::class => [
                MethodCallToReturnRector::METHOD_CALL_WRAPS => [new MethodCallToReturn(ReturnDeny::class, 'deny')],
            ],
        ];
    }
}
