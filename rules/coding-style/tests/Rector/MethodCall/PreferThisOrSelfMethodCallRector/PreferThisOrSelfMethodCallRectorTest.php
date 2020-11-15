<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector;

use Iterator;
use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;
use Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PreferThisOrSelfMethodCallRectorTest extends AbstractRectorTestCase
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
            PreferThisOrSelfMethodCallRector::class => [
                PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                    AbstractTestCase::class => 'self',
                    BeLocalClass::class => 'this',
                    TestCase::class => 'self',
                ],
            ],
        ];
    }
}
