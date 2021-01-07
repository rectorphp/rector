<?php

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;

final class AddPackageToRequireTest extends TestCase
{
    public function testAddNonExistingPackage(): void
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
                'vendor1/package3' => '^3.0',
            ],
        ];

        $addPackageToRequire = new AddPackageToRequire('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $addPackageToRequire->modify($composerData));
    }

    public function testAddExistingPackage(): void
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

        $addPackageToRequire = new AddPackageToRequire('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerData, $addPackageToRequire->modify($composerData));
    }

    public function testAddExistingDevPackage(): void
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
                'vendor1/package2' => '^2.0',
            ],
        ];

        $addPackageToRequire = new AddPackageToRequire('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerData, $addPackageToRequire->modify($composerData));
    }
}
