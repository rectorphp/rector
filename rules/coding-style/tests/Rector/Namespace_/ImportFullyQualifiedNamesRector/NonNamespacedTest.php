<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Class_\RenameClassRector;

/**
 * @see \Rector\PostRector\Rector\NameImportingPostRector
 */
final class NonNamespacedTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);

        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureNonNamespaced');
    }

    protected function getRectorClass(): string
    {
        return RenameClassRector::class;
    }
}
