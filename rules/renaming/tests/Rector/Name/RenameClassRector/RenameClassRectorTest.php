<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector;

use Iterator;
use Manual\Twig\TwigFilter;
use Manual_Twig_Filter;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\DuplicatedClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\AbstractManualExtension;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClassWithoutTypo;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClassWithTypo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameClassRectorTest extends AbstractRectorTestCase
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
     * @see https://github.com/rectorphp/rector/issues/1438
     */
    public function testClassNameDuplication(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/FixtureDuplication/skip_duplicated_class.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassRector::class => [
                RenameClassRector::OLD_TO_NEW_CLASSES => [
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
                    'Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture\SingularClass' => DuplicatedClass::class,
                ],
            ],
        ];
    }
}
