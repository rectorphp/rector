<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector;

use Iterator;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\DifferentClass;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\LocalFormEvents;
use Rector\Renaming\ValueObject\RenameClassConstant;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameClassConstantRectorTest extends AbstractRectorTestCase
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
            RenameClassConstantRector::class => [
                RenameClassConstantRector::CLASS_CONSTANT_RENAME => [
                    new RenameClassConstant(LocalFormEvents::class, 'PRE_BIND', 'PRE_SUBMIT'),
                    new RenameClassConstant(LocalFormEvents::class, 'BIND', 'SUBMIT'),
                    new RenameClassConstant(LocalFormEvents::class, 'POST_BIND', 'POST_SUBMIT'),
                    new RenameClassConstant(
                        LocalFormEvents::class,
                        'OLD_CONSTANT',
                        DifferentClass::class . '::NEW_CONSTANT'
                    ),
                ],
            ],
        ];
    }
}
