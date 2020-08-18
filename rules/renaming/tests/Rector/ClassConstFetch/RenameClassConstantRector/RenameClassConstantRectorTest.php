<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\DifferentClass;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstantRector\Source\LocalFormEvents;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassConstantRector::class => [
                RenameClassConstantRector::OLD_TO_NEW_CONSTANTS_BY_CLASS => [
                    LocalFormEvents::class => [
                        'PRE_BIND' => 'PRE_SUBMIT',
                        'BIND' => 'SUBMIT',
                        'POST_BIND' => 'POST_SUBMIT',
                        'OLD_CONSTANT' => DifferentClass::class . '::NEW_CONSTANT',
                    ],
                ],
            ],
        ];
    }
}
