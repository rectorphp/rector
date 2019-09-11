<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector;

use Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector;
use Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector\Source\VarDumperTrait;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VarDumperTestTraitMethodArgsRectorTest extends AbstractRectorTestCase
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
            VarDumperTestTraitMethodArgsRector::class => [
                '$traitName' => VarDumperTrait::class,
            ],
        ];
    }
}
