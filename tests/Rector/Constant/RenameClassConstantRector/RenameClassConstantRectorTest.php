<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Constant\RenameClassConstantRector;

use Rector\Rector\Constant\RenameClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\DifferentClass;
use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\LocalFormEvents;

final class RenameClassConstantRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassConstantRector::class => [
                'oldToNewConstantsByClass' => [
                    LocalFormEvents::class => [
                        'PRE_BIND' => 'PRE_SUBMIT',
                        'BIND' => 'SUBMIT',
                        'POST_BIND' => 'POST_SUBMIT',
                        'OLD_CONSTANT' => DifferentClass::class . '::NEW_CONSTANT',
                    ],
                ],
            ],
        ];
    }
}
