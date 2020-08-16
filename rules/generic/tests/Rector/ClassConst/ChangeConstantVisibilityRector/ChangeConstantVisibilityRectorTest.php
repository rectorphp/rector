<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Source\ParentObject;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeConstantVisibilityRectorTest extends AbstractRectorTestCase
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
            ChangeConstantVisibilityRector::class => [
                ChangeConstantVisibilityRector::CONSTANT_TO_VISIBILITY_BY_CLASS => [
                    ParentObject::class => [
                        'TO_BE_PUBLIC_CONSTANT' => 'public',
                        'TO_BE_PROTECTED_CONSTANT' => 'protected',
                        'TO_BE_PRIVATE_CONSTANT' => 'private',
                    ],
                    'Rector\Generic\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture\AnotherClassWithInvalidConstants' => [
                        'TO_BE_PRIVATE_CONSTANT' => 'private',
                    ],
                ],
            ],
        ];
    }
}
