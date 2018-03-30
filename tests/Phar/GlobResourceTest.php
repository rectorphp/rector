<?php declare(strict_types=1);

namespace Rector\Tests\Phar;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\GlobResource;

final class GlobResourceTest extends TestCase
{
    public function testGlobResource(): void
    {
        $resource = new GlobResource(__DIR__ . '/Source/src/', '', true);
        $resourcePhar = new GlobResource('phar://' . __DIR__ . '/Source/rector.phar/src/', '', true);

        $this->assertSame(iterator_count($resource), iterator_count($resourcePhar));
    }
}
