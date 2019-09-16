<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector;

use Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector;
use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyWebTestCaseAssertionsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/response_code_same.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SimplifyWebTestCaseAssertionsRector::class => [
                '$webTestCaseClass' => FixtureWebTestCase::class,
            ],
        ];
    }
}
