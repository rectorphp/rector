<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\RedirectToRouteRector;

use Rector\Symfony\Rector\Controller\RedirectToRouteRector;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RedirectToRouteRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RedirectToRouteRector::class => [
                '$controllerClass' => SymfonyController::class,
            ],
        ];
    }
}
