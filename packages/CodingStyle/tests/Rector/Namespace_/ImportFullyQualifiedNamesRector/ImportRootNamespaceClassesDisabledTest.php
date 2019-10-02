<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ImportRootNamespaceClassesDisabledTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/import_root_namespace_classes_disabled.php.inc'];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            ImportFullyQualifiedNamesRector::class => [
                '$shouldImportRootNamespaceClasses' => false,
            ],
        ];
    }
}
