<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector;

use Iterator;
use Rector\Symfony\Rector\Form\FormIsValidRector;
use Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Source\Form;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormIsValidRectorTest extends AbstractRectorTestCase
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
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FormIsValidRector::class => [
                '$formClass' => Form::class,
            ],
        ];
    }
}
