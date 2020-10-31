<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceParentCallByPropertyCallRectorTest extends AbstractRectorTestCase
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
            ReplaceParentCallByPropertyCallRector::class => [
                ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => [
                    new ReplaceParentCallByPropertyCall(
                        TypeClassToReplaceMethodCallBy::class,
                        'someMethod',
                        'someProperty'
                    ),
                ],
            ],
        ];
    }
}
