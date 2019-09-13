<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Expression\RedirectorToRedirectToUrlRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ZendToSymfony\Rector\Expression\RedirectorToRedirectToUrlRector;

final class RedirectorToRedirectToUrlRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/redirector.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RedirectorToRedirectToUrlRector::class;
    }
}
