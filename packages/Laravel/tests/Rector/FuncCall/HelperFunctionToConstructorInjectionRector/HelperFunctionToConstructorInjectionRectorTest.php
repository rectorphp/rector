<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;

use Rector\Laravel\Rector\FuncCall\HelperFunctionToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class HelperFunctionToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/view.php.inc', __DIR__ . '/Fixture/broadcast.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return HelperFunctionToConstructorInjectionRector::class;
    }
}
