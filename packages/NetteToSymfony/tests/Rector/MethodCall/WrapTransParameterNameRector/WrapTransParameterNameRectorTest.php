<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector;

use Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\SomeTranslator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class WrapTransParameterNameRectorTest extends AbstractRectorTestCase
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
            WrapTransParameterNameRector::class => [
                '$translatorClass' => SomeTranslator::class,
            ],
        ];
    }
}
