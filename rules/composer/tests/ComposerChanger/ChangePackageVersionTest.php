<?php

namespace Rector\Composer\Tests\ComposerChanger;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerChanger\ChangePackageVersion;

final class ChangePackageVersionTest extends TestCase
{
    public function testChangeVersionNonExistingPackage()
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = $composerData;

        $changePackageVersion = new ChangePackageVersion('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $changePackageVersion->process($composerData));
    }

    public function testChangeVersionExistingPackage()
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
        $this->assertEquals($changedComposerData, $changePackageVersion->process($composerData));
    }

    public function testChangeVersionExistingDevPackage()
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
        $this->assertEquals($changedComposerData, $changePackageVersion->process($composerData));
    }
}
