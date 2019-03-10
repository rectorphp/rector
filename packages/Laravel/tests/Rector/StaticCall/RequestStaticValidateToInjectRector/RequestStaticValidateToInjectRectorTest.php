<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector;

use Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RequestStaticValidateToInjectRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/function.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RequestStaticValidateToInjectRector::class;
    }
}
