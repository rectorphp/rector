<?php

namespace Rector\Composer\Tests\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerModifier\ChangePackage;

final class ChangePackageTest extends TestCase
{
    public function testChangeNonExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = $composerData;

        $changePackage = new ChangePackage('vendor1/package3', 'vendor1/package4', '^3.0');
        $this->assertEquals($changedComposerData, $changePackage->modify($composerData));
    }

    public function testChangeExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package3' => '^3.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changePackage = new ChangePackage('vendor1/package1', 'vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $changePackage->modify($composerData));
    }

    public function testChangeExistingDevPackage(): void
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
                'vendor1/package3' => '^3.0',
            ],
        ];

        $changePackage = new ChangePackage('vendor1/package2', 'vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $changePackage->modify($composerData));
    }
}
