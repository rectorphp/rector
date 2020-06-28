<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector;

use Iterator;
use Rector\Core\Rector\MethodCall\MethodCallToReturnRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;
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
                '$methodNamesByType' => [
                    ReturnDeny::class => ['deny'],
                ],
            ],
        ];
    }
}
