<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\String_\SensitiveHereNowDocRector;

use Rector\Php\Rector\String_\SensitiveHereNowDocRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SensitiveHereNowDocRectorTest extends AbstractRectorTestCase
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
        return SensitiveHereNowDocRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}
