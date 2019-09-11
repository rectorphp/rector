<?php declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector;

use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;
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
            MessageAsArrayRector::class => [
                '$messageType' => MessageType::class,
            ],
        ];
    }
}
