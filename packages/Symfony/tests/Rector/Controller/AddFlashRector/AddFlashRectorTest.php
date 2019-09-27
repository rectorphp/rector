<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector;

use Iterator;
use Rector\Symfony\Rector\Controller\AddFlashRector;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddFlashRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddFlashRector::class => [
                '$controllerClass' => SymfonyController::class,
            ],
        ];
    }
}
