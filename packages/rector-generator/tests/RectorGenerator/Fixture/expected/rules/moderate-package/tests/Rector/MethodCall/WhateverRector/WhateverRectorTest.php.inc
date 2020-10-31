<?php

declare(strict_types=1);

namespace Rector\ModeratePackage\Tests\Rector\MethodCall\WhateverRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class WhateverRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\ModeratePackage\Rector\MethodCall\WhateverRector::class =>
                [\Rector\ModeratePackage\Rector\MethodCall\WhateverRector::CLASS_TYPE_TO_METHOD_NAME => ['SomeClass' => 'configure']]
        ];
    }
}
