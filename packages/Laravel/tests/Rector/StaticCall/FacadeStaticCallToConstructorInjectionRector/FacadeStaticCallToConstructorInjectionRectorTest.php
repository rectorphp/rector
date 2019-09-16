<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;

use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FacadeStaticCallToConstructorInjectionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return FacadeStaticCallToConstructorInjectionRector::class;
    }
}
