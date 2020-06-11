<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Manual\Twig\TwigFilter;
use Manual_Twig_Filter;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Fixture\DuplicatedClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\AbstractManualExtension;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClassWithoutTypo;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClassWithTypo;

final class RenameClassRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @see https://github.com/rectorphp/rector/issues/1438
     */
    public function testClassNameDuplication(): void
    {
        $this->doTestFile(__DIR__ . '/FixtureDuplication/skip_duplicated_class.php.inc');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassRector::class => [
                '$oldToNewClasses' => [
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
