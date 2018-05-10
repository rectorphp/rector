<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector
 */
final class InvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Kernel class "App\Kernel" provided in "parameters > kernel_class" is not autoloadable.'
            . ' Make sure composer.json of your application is valid and rector is loading "vendor/autoload.php" of your application.'
        );

        $this->doTestFileMatchesExpectedContent(__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config.yml';
    }
}
