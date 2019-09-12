<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector;

use Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ActionSuffixRemoverRectorTest extends AbstractRectorTestCase
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
        return ActionSuffixRemoverRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }
}
