<?php declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;

use Rector\Shopware\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceEnlightResponseWithSymfonyResponseRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ReplaceEnlightResponseWithSymfonyResponseRector::class;
    }
}
