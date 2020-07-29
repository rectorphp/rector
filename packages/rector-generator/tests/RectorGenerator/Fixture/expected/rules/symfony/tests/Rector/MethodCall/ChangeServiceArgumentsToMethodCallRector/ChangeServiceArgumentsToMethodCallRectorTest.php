<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeServiceArgumentsToMethodCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\Symfony\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector::class =>
                [\Rector\Symfony\Rector\MethodCall\ChangeServiceArgumentsToMethodCallRector::CLASS_TYPE_TO_METHOD_NAME => ['SomeClass' => 'configure']]
        ];
    }
}
