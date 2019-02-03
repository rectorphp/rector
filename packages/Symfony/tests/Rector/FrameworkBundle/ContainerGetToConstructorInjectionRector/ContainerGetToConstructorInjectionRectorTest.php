<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContainerGetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
