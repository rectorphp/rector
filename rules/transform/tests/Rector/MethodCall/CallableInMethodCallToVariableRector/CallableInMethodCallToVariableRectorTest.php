<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\MethodCall\CallableInMethodCallToVariableRector;
use Rector\Transform\Tests\Rector\MethodCall\CallableInMethodCallToVariableRector\Source\DummyCache;
use Rector\Transform\ValueObject\CallableInMethodCallToVariable;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CallableInMethodCallToVariableRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            CallableInMethodCallToVariableRector::class => [
                CallableInMethodCallToVariableRector::CALLABLE_IN_METHOD_CALL_TO_VARIABLE => [
                    new CallableInMethodCallToVariable(DummyCache::class, 'save', 1),
                ],
            ],
        ];
    }
}
