<?php

namespace Rector\Composer\Tests\ComposerChanger;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerChanger\RemovePackage;

final class RemovePackageTest extends TestCase
{
    public function testRemoveNonExistingPackage()
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = $composerData;

        $removePackage = new RemovePackage('vendor1/package3');
        $this->assertEquals($changedComposerData, $removePackage->process($composerData));
    }

    public function testRemoveExistingPackage()
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
        $this->assertEquals($changedComposerData, $removePackage->process($composerData));
    }

    public function testRemoveExistingDevPackage()
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
        $this->assertEquals($changedComposerData, $removePackage->process($composerData));
    }
}
