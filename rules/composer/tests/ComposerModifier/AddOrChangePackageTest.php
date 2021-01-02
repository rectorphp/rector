<?php

namespace Rector\Composer\Tests\ComposerModifier;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerModifier\AddOrChangePackage;

final class AddOrChangePackageTest extends TestCase
{
    public function testAddOrChangeNonExistingPackage()
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
        ];;

        $addOrChangePackage = new AddOrChangePackage('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $addOrChangePackage->modify($composerData));
    }

    public function testAddOrChangeExistingPackage()
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
        ];;

        $addOrChangePackage = new AddOrChangePackage('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerData, $addOrChangePackage->modify($composerData));
    }

    public function testAddOrChangeExistingDevPackage()
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
                'vendor1/package2' => '^3.0',
            ],
        ];;

        $addOrChangePackage = new AddOrChangePackage('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerData, $addOrChangePackage->modify($composerData));
    }

    public function testAddOrChangeOrChangePackageToUnknownSection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "require", "require-dev". Got: "require_dev"');
        new AddOrChangePackage('vendor1/package1', '^1.0', 'require_dev');
    }
}
