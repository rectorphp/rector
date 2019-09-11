<?php declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;

use Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceEnlightResponseWithSymfonyResponseRectorTest extends AbstractRectorTestCase
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
        return ReplaceEnlightResponseWithSymfonyResponseRector::class;
    }
}
