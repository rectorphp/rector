<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector;

use Iterator;
use Rector\Symfony\Rector\Form\FormTypeGetParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormTypeGetParentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/abstract_type.php.inc'];
        yield [__DIR__ . '/Fixture/abstract_type_extension.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return FormTypeGetParentRector::class;
    }
}
