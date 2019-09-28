<?php declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Rector\Configuration\Option;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NamePostImportTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $filePath): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);
        $this->doTestFile($filePath);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/class_to_new_with_post_import.php.inc'];
    }

    /**
     * @return string[]
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
