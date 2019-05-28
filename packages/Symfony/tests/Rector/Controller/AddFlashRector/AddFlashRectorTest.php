<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\AddFlashRector;

use Rector\Symfony\Rector\Controller\AddFlashRector;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddFlashRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
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
