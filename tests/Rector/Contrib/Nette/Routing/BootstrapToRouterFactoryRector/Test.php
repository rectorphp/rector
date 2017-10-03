<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Routing\BootstrapToRouterFactoryRector;

use Rector\Rector\Contrib\Nette\Routing\BootstrapToRouterFactoryRector;
use Rector\Rector\Contrib\Nette\Routing\CleanupBootstrapToRouterFactoryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/bootstrap.php',
            __DIR__ . '/Correct/correct.php.inc'
        );
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
