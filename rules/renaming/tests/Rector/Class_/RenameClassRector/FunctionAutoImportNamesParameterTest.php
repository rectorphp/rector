<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;

/**
 * @see \Rector\PostRector\Rector\NameImportingRector
 */
final class FunctionAutoImportNamesParameterTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImportNamesFunction');
    }

    protected function getAutoImportNames(): ?bool
    {
        return true;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassRector::class => [
                '$oldToNewClasses' => [
                    OldClass::class => NewClass::class,
                ],
            ],
        ];
    }
}
