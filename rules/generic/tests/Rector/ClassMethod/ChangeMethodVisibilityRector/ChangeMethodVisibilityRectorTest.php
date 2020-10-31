<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;
use Rector\Generic\ValueObject\ChangeMethodVisibility;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ChangeMethodVisibilityRector::class => [
                ChangeMethodVisibilityRector::METHOD_VISIBILITIES => [
                    new ChangeMethodVisibility(ParentObject::class, 'toBePublicMethod', 'public'),
                    new ChangeMethodVisibility(ParentObject::class, 'toBeProtectedMethod', 'protected'),
                    new ChangeMethodVisibility(ParentObject::class, 'toBePrivateMethod', 'private'),
                    new ChangeMethodVisibility(ParentObject::class, 'toBePublicStaticMethod', 'public'),
                ],
            ],
        ];
    }
}
