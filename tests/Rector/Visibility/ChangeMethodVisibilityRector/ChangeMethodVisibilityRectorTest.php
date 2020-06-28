<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Visibility\ChangeMethodVisibilityRector;

use Iterator;
use Rector\Core\Rector\Visibility\ChangeMethodVisibilityRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeMethodVisibilityRectorTest extends AbstractRectorTestCase
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
            ChangeMethodVisibilityRector::class => [
                '$methodToVisibilityByClass' => [
                    ParentObject::class => [
                        'toBePublicMethod' => 'public',
                        'toBeProtectedMethod' => 'protected',
                        'toBePrivateMethod' => 'private',
                        'toBePublicStaticMethod' => 'public',
                    ],
                ],
            ],
        ];
    }
}
