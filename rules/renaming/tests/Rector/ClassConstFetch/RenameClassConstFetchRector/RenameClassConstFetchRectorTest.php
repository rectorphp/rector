<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector;

use Iterator;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\DifferentClass;
use Rector\Renaming\Tests\Rector\ClassConstFetch\RenameClassConstFetchRector\Source\LocalFormEvents;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameClassConstFetchRectorTest extends AbstractRectorTestCase
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
            RenameClassConstFetchRector::class => [
                RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => [
                    new RenameClassConstFetch(LocalFormEvents::class, 'PRE_BIND', 'PRE_SUBMIT'),
                    new RenameClassConstFetch(LocalFormEvents::class, 'BIND', 'SUBMIT'),
                    new RenameClassConstFetch(LocalFormEvents::class, 'POST_BIND', 'POST_SUBMIT'),
                    new RenameClassAndConstFetch(
                        LocalFormEvents::class,
                        'OLD_CONSTANT',
                        DifferentClass::class,
                        'NEW_CONSTANT'
                    ),
                ],
            ],
        ];
    }
}
