<?php

namespace Rector\Composer\Tests\ComposerChanger;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerChanger\ChangePackage;

final class ChangePackageTest extends TestCase
{
    public function testChangeNonExistingPackage()
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = $composerData;

        $changePackage = new ChangePackage('vendor1/package3', 'vendor1/package4', '^3.0');
        $this->assertEquals($changedComposerData, $changePackage->process($composerData));
    }

    public function testChangeExistingPackage()
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
        $this->assertEquals($changedComposerData, $changePackage->process($composerData));
    }

    public function testChangeExistingDevPackage()
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
        $this->assertEquals($changedComposerData, $changePackage->process($composerData));
    }
}
