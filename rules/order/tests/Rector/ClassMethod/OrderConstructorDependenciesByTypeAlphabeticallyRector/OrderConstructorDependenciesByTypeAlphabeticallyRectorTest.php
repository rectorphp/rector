<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector;

use Iterator;
use Rector\Order\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderConstructorDependenciesByTypeAlphabeticallyRectorTest extends AbstractRectorTestCase
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
            OrderConstructorDependenciesByTypeAlphabeticallyRector::class => [
                OrderConstructorDependenciesByTypeAlphabeticallyRector::SKIP_PATTERNS => ['*skip_pattern_setting*'],
            ],
        ];
    }
}
