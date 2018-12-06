<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
