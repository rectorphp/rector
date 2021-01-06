<?php

namespace Rector\Composer\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ReplacePackage;

final class ReplacePackageTest extends TestCase
{
    public function testReplaceNonExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $replacedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $replacePackage = new ReplacePackage('vendor1/package3', 'vendor1/package4', '^3.0');
        $this->assertEquals($replacedComposerData, $replacePackage->modify($composerData));
    }

    public function testReplaceExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $replacedComposerData = [
            'require' => [
                'vendor1/package3' => '^3.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $replacePackage = new ReplacePackage('vendor1/package1', 'vendor1/package3', '^3.0');
        $this->assertEquals($replacedComposerData, $replacePackage->modify($composerData));
    }

    public function testReplaceExistingDevPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package2' => '^2.0',
            ],
        ];

        $replacedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package3' => '^3.0',
            ],
        ];

        $replacePackage = new ReplacePackage('vendor1/package2', 'vendor1/package3', '^3.0');
        $this->assertEquals($replacedComposerData, $replacePackage->modify($composerData));
    }
}
