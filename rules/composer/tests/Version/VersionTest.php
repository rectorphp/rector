<?php

namespace Rector\Composer\Tests\Version;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\Version\Version;
use UnexpectedValueException;

final class VersionTest extends TestCase
{
    public function testExactVersion(): void
    {
        $version = new Version('1.2.3');
        $this->assertInstanceOf(Version::class, $version);
        $this->assertEquals('1.2.3', $version->getVersion());
    }

    public function testTildeVersion(): void
    {
        $version = new Version('~1.2.3');
        $this->assertInstanceOf(Version::class, $version);
        $this->assertEquals('~1.2.3', $version->getVersion());
    }

    public function testCaretVersion(): void
    {
        $version = new Version('^1.2.3');
        $this->assertInstanceOf(Version::class, $version);
        $this->assertEquals('^1.2.3', $version->getVersion());
    }

    public function testDevMasterAsVersion(): void
    {
        $version = new Version('dev-master as 1.2.3');
        $this->assertInstanceOf(Version::class, $version);
        $this->assertEquals('dev-master as 1.2.3', $version->getVersion());
    }

    public function testWrongAliasVersion(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Could not parse version constraint AS: Invalid version string "AS"');
        new Version('dev-master AS 1.2.3');
    }
}
