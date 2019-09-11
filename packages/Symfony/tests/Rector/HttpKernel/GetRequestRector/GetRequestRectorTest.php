<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector;

use Rector\Symfony\Rector\HttpKernel\GetRequestRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetRequestRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return GetRequestRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/skip_event_subscriber.php.inc'];
    }
}
