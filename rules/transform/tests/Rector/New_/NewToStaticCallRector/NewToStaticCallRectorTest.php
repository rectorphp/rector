<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\New_\NewToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewToStaticCallRectorTest extends AbstractRectorTestCase
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
            NewToStaticCallRector::class => [
                NewToStaticCallRector::TYPE_TO_STATIC_CALLS => [
                    new NewToStaticCall(FromNewClass::class, IntoStaticClass::class, 'run'),
                ],
            ],
        ];
    }
}
