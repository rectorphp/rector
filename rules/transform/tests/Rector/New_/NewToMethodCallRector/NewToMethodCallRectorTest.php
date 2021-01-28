<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\New_\NewToMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClass;
use Rector\Transform\Tests\Rector\New_\NewToMethodCallRector\Source\MyClassFactory;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewToMethodCallRectorTest extends AbstractRectorTestCase
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
            NewToMethodCallRector::class => [
                NewToMethodCallRector::NEWS_TO_METHOD_CALLS => [
                    new NewToMethodCall(MyClass::class, MyClassFactory::class, 'create'),
                ],
            ],
        ];
    }
}
