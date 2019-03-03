<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;

use Rector\Laravel\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FacadeStaticCallToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FacadeStaticCallToConstructorInjectionRector::class;
    }
}
