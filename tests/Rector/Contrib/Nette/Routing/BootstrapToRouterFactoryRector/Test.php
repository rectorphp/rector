<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Routing\BootstrapToRouterFactoryRector;

use Rector\Rector\Contrib\Nette\Routing\BootstrapToRouterFactoryRector;
use Rector\Rector\Contrib\Nette\Routing\CleanupBootstrapToRouterFactoryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Test extends AbstractRectorTestCase
{
    /**
     * @var string
     */
    private const GENERATED_ROUTER_FACTORY_FILE = __DIR__ . '/Wrong/RouterFactory.php';

    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/bootstrap.php',
            __DIR__ . '/Correct/correct.php.inc'
        );

        $this->assertFileExists(self::GENERATED_ROUTER_FACTORY_FILE);
        $this->assertFileEquals(
            self::GENERATED_ROUTER_FACTORY_FILE,
            __DIR__ . '/Correct/RouterFactory.php.expected.inc'
        );
    }

    protected function tearDown(): void
    {
        unlink(self::GENERATED_ROUTER_FACTORY_FILE);
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [
            BootstrapToRouterFactoryRector::class,
            CleanupBootstrapToRouterFactoryRector::class,
        ];
    }
}
