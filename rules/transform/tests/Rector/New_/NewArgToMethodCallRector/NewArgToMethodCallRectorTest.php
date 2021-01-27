<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewArgToMethodCallRectorTest extends AbstractRectorTestCase
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
            NewArgToMethodCallRector::class => [
                NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => [
                    new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv'),
                ],
            ],
        ];
    }
}
