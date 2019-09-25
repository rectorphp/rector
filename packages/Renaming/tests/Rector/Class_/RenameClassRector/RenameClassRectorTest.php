<?php declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Manual\Twig\TwigFilter;
use Manual_Twig_Filter;
use Rector\Renaming\Exception\InvalidPhpCodeException;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\DuplicatedClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClassWithTypo;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     * @dataProvider provideDataForTestWithClassAnnotation()
     * @dataProvider provideDataForTestWithNamespaceRename()
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/class_to_new.php.inc'];
        yield [__DIR__ . '/Fixture/class_to_interface.php.inc'];
        yield [__DIR__ . '/Fixture/interface_to_class.php.inc'];
        yield [__DIR__ . '/Fixture/name_insensitive.php.inc'];
        yield [__DIR__ . '/Fixture/underscore_doc.php.inc'];
        yield [__DIR__ . '/Fixture/keep_return_tag.php.inc'];
        yield [__DIR__ . '/Fixture/rename_trait.php.inc'];
        yield [__DIR__ . '/Fixture/fqnize_namespaced.php.inc'];
    }

    public function provideDataForTestWithClassAnnotation(): Iterator
    {
        yield [__DIR__ . '/Fixture/class_annotations.php.inc'];
        yield [__DIR__ . '/Fixture/class_annotations_serializer_type.php.inc'];
    }

    public function provideDataForTestWithNamespaceRename(): Iterator
    {
        yield [__DIR__ . '/Fixture/rename_class_without_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/rename_class.php.inc'];
        yield [__DIR__ . '/Fixture/rename_interface.php.inc'];
        yield [__DIR__ . '/Fixture/rename_class_without_namespace_to_class_without_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/rename_class_to_class_without_namespace.php.inc'];
    }

    /**
     * @see https://github.com/rectorphp/rector/issues/1438
     */
    public function testClassNameDuplication(): void
    {
        $this->expectException(InvalidPhpCodeException::class);
        $this->doTestFile(__DIR__ . '/Fixture/skip_duplicated_class.php.inc');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassRector::class => [
                'oldToNewClasses' => [
                    'FqnizeNamespaced' => 'Abc\FqnizeNamespaced',
                    OldClass::class => NewClass::class,
                    OldClassWithTypo::class => NewClassWithoutTypo::class,
                    'DateTime' => 'DateTimeInterface',
                    'Countable' => 'stdClass',
                    Manual_Twig_Filter::class => TwigFilter::class,
                    'Twig_AbstractManualExtension' => AbstractManualExtension::class,
                    'Twig_Extension_Sandbox' => 'Twig\Extension\SandboxExtension',
                    // Renaming class itself and its namespace
                    'MyNamespace\MyClass' => 'MyNewNamespace\MyNewClass',
                    'MyNamespace\MyTrait' => 'MyNewNamespace\MyNewTrait',
                    'MyNamespace\MyInterface' => 'MyNewNamespace\MyNewInterface',
                    'MyOldClass' => 'MyNamespace\MyNewClass',
                    'AnotherMyOldClass' => 'AnotherMyNewClass',
                    'MyNamespace\AnotherMyClass' => 'MyNewClassWithoutNamespace',
                    // test duplicated class - @see https://github.com/rectorphp/rector/issues/1438
                    'Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\SingularClass' => DuplicatedClass::class,
                ],
            ],
        ];
    }
}
