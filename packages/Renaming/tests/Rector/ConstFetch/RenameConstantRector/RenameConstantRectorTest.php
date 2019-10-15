<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ConstFetch\RenameConstantRector;

use Iterator;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameConstantRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/spaghetti.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameConstantRector::class => [
                '$oldToNewConstants' => [
                    'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                    'OLD_CONSTANT' => 'NEW_CONSTANT',
                ],
            ],
        ];
    }
}
