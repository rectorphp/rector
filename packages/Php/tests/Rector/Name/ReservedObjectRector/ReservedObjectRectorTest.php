<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector;

use Rector\Php\Rector\Name\ReservedObjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReservedObjectRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/ReservedObject.php'];
        yield [__DIR__ . '/Fixture/skip_type_declaration_object.php'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReservedObjectRector::class => [
                '$reservedKeywordsToReplacements' => [
                    'ReservedObject' => 'SmartObject',
                    'Object' => 'AnotherSmartObject',
                ],
            ],
        ];
    }
}
