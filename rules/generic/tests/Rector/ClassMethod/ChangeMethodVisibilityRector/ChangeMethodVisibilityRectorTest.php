<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;
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
                ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
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
