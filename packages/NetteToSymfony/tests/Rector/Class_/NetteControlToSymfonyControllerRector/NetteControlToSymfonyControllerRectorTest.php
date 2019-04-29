<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector;

use Rector\NetteToSymfony\Rector\Class_\NetteControlToSymfonyControllerRector;
use Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Source\NetteControl;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteControlToSymfonyControllerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [NetteControlToSymfonyControllerRector::class => [
            '$netteControlClass' => NetteControl::class,
        ]];
    }
}
