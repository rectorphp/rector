<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector;

use Rector\Symfony\Rector\Form\StringFormTypeToClassRector;
use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringFormTypeToClassRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            StringFormTypeToClassRector::class => [
                '$formBuilderClass' => FormBuilder::class,
            ],
        ];
    }
}
