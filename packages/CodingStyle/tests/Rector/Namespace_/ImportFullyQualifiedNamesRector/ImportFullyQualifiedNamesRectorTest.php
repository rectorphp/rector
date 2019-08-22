<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Iterator;
use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ImportFullyQualifiedNamesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideNamespacedClasses()
     * @dataProvider provideFunctions()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideFunctions(): Iterator
    {
        yield [__DIR__ . '/Fixture/import_function.php.inc'];
        yield [__DIR__ . '/Fixture/import_function_no_class.php.inc'];
        yield [__DIR__ . '/Fixture/import_return_doc.php.inc'];
    }

    public function provideNamespacedClasses(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/double_import.php.inc'];
        yield [__DIR__ . '/Fixture/double_import_with_existing.php.inc'];
        yield [__DIR__ . '/Fixture/already_with_use.php.inc'];
        yield [__DIR__ . '/Fixture/already_class_name.php.inc'];
        yield [__DIR__ . '/Fixture/no_class.php.inc'];
        yield [__DIR__ . '/Fixture/short.php.inc'];

        // keep
        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/keep_aliased.php.inc'];
        yield [__DIR__ . '/Fixture/keep_same_end.php.inc'];
        yield [__DIR__ . '/Fixture/keep_trait_use.php.inc'];

        // php doc
        yield [__DIR__ . '/Fixture/import_param_doc.php.inc'];
        yield [__DIR__ . '/Fixture/doc_combined.php.inc'];
        yield [__DIR__ . '/Fixture/conflicting_endings.php.inc'];
        yield [__DIR__ . '/Fixture/already_class_name_in_param_doc.php.inc'];

        // buggy
        yield [__DIR__ . '/Fixture/many_imports.php.inc'];
        yield [__DIR__ . '/Fixture/keep_static_method.php.inc'];
        yield [__DIR__ . '/Fixture/keep_various_request.php.inc'];
        yield [__DIR__ . '/Fixture/instance_of.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}
