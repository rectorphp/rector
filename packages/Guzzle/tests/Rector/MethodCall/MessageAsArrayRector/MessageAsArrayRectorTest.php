<?php declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector;

use Iterator;
use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MessageAsArrayRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return MessageAsArrayRector::class;
    }
}
