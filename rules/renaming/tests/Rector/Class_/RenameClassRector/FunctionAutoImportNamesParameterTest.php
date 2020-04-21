<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;

/**
 * @see \Rector\PostRector\Rector\NameImportingPostRector
 */
final class FunctionAutoImportNamesParameterTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);

        $this->doTestFile($filePath);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImportNamesFunction');
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
