<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application;

use PHPUnit\Framework\TestCase;
use Rector\Core\Application\VersionResolver;

final class VersionResolverTest extends TestCase
{
    public function test(): void
    {
        $packageVersion = VersionResolver::resolvePackageVersion();

        // should be a commit hash size, as we're in untagged test
        $this->assertSame(40, strlen($packageVersion));
    }
}
