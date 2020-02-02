<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Configuration\Option;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ImportRootNamespaceClassesDisabledTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->setParameter(Option::IMPORT_SHORT_CLASSES_PARAMETER, false);
        $this->doTestFile($file);
    }

    public function provideData(): iterable
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureRoot');
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}
