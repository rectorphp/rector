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

    public function providerPartials(): Iterator
    {
        // @todo fix later, details
        yield [__DIR__ . '/Fixture/doc_combined.php.inc'];
        yield [__DIR__ . '/Fixture/conflicting_endings.php.inc'];
        yield [__DIR__ . '/Fixture/import_return_doc.php.inc'];
    }

    public function provideNamespacedClasses(): Iterator
    {
        // keep
        yield [__DIR__ . '/Fixture/bootstrap_names.php.inc'];

        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/keep_aliased.php.inc'];
        yield [__DIR__ . '/Fixture/keep_same_end.php.inc'];
        yield [__DIR__ . '/Fixture/keep_same_end_with_existing_import.php.inc'];
        yield [__DIR__ . '/Fixture/keep_trait_use.php.inc'];
        yield [__DIR__ . '/Fixture/short.php.inc'];

        // same short class with namespace
        yield [__DIR__ . '/Fixture/skip_same_namespaced_used_class.php.inc'];
        yield [__DIR__ . '/Fixture/include_used_local_class.php.inc'];

        // the class in the same namespace exists, but it is not used in this file, so we can skip it
        yield [__DIR__ . '/Fixture/same_namespaced_class.php.inc'];

        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/double_import.php.inc'];
        yield [__DIR__ . '/Fixture/double_import_with_existing.php.inc'];
        yield [__DIR__ . '/Fixture/already_with_use.php.inc'];
        yield [__DIR__ . '/Fixture/already_class_name.php.inc'];
        yield [__DIR__ . '/Fixture/no_class.php.inc'];

        // php doc
        yield [__DIR__ . '/Fixture/import_param_doc.php.inc'];
        yield [__DIR__ . '/Fixture/already_class_name_in_param_doc.php.inc'];

        // buggy
        yield [__DIR__ . '/Fixture/many_imports.php.inc'];
        yield [__DIR__ . '/Fixture/keep_static_method.php.inc'];
        yield [__DIR__ . '/Fixture/keep_various_request.php.inc'];
        yield [__DIR__ . '/Fixture/instance_of.php.inc'];
        yield [__DIR__ . '/Fixture/should_keep_all_doc_blocks_annotations_parameters.php.inc'];
        yield [__DIR__ . '/Fixture/should_not_break_doctrine_inverse_join_columns_annotations.php.inc'];

        yield [__DIR__ . '/Fixture/import_root_namespace_classes_enabled.php.inc'];
    }

    public function provideFunctions(): Iterator
    {
        yield [__DIR__ . '/Fixture/import_function.php.inc'];
        yield [__DIR__ . '/Fixture/import_function_no_class.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}
