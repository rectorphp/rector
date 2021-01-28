<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\ClassConstFetch\ClassConstFetchToStringRector;
use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Source\OldClassWithConstants;
use Rector\Transform\ValueObject\ClassConstFetchToValue;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassConstFetchToStringRectorTest extends AbstractRectorTestCase
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
            ClassConstFetchToStringRector::class => [
                ClassConstFetchToStringRector::CLASS_CONST_FETCHES_TO_VALUES => [
                    new ClassConstFetchToValue(OldClassWithConstants::class, 'DEVELOPMENT', 'development'),
                    new ClassConstFetchToValue(OldClassWithConstants::class, 'PRODUCTION', 'production'),
                ],
            ],
        ];
    }
}
