<?php

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;

final class ChangePackageVersionTest extends TestCase
{
    public function testChangeVersionNonExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changePackageVersion = new ChangePackageVersion('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $changePackageVersion->modify($composerData));
    }

    public function testChangeVersionExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package1' => '^3.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changePackageVersion = new ChangePackageVersion('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerData, $changePackageVersion->modify($composerData));
    }

    public function testChangeVersionExistingDevPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package2' => '^3.0',
            ],
        ];

        $changePackageVersion = new ChangePackageVersion('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerData, $changePackageVersion->modify($composerData));
    }
}
