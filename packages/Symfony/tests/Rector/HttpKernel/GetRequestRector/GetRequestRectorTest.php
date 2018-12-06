<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\HttpKernel\GetRequestRector;

use Rector\Symfony\Rector\HttpKernel\GetRequestRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetRequestRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return GetRequestRector::class;
    }
}
