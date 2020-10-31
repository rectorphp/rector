<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector;

use Iterator;
use Rector\SymfonyPhpConfig\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector;
use Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector\Source\ClassPassedAsId;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeServiceArgumentsToMethodCallRectorTest extends AbstractRectorTestCase
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
            ChangeServiceArgumentsToMethodCallRector::class => [
                ChangeServiceArgumentsToMethodCallRector::CLASS_TYPE_TO_METHOD_NAME => [
                    ClassPassedAsId::class => 'configure',
                ],
            ],
        ];
    }
}
