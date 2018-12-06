<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use function Safe\sprintf;

final class SecondInvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'Kernel class "%s" provided in "parameters > %s" is not autoloadable. ' .
            'Make sure composer.json of your application is valid and rector is loading "vendor/autoload.php" of your application.',
            'NonExistingClass',
            'kernel_class'
        ));

        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config-2.yml';
    }
}
