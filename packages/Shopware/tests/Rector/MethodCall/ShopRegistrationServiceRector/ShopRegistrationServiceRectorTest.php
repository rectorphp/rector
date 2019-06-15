<?php declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\MethodCall\ShopRegistrationServiceRector;

use Rector\Shopware\Rector\MethodCall\ShopRegistrationServiceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ShopRegistrationServiceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ShopRegistrationServiceRector::class;
    }
}
