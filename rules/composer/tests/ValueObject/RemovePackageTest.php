<?php

namespace Rector\Composer\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\RemovePackage;

final class RemovePackageTest extends TestCase
{
    public function testRemoveNonExistingPackage(): void
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

        $removePackage = new RemovePackage('vendor1/package3');
        $this->assertEquals($changedComposerData, $removePackage->modify($composerData));
    }

    public function testRemoveExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package2' => '^2.0',
            ],
        ];

        $removePackage = new RemovePackage('vendor1/package1');
        $this->assertEquals($changedComposerData, $removePackage->modify($composerData));
    }

    public function testRemoveExistingDevPackage(): void
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
        ];

        $removePackage = new RemovePackage('vendor1/package2');
        $this->assertEquals($changedComposerData, $removePackage->modify($composerData));
    }
}
