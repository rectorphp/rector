<?php

namespace Rector\Composer\Tests\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\AddPackage;

final class AddPackageTest extends TestCase
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
        ];;

        $addPackage = new AddPackage('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerData, $addPackage->modify($composerData));
    }

    public function testAddExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = $composerData;

        $addPackage = new AddPackage('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerData, $addPackage->modify($composerData));
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

        $changedComposerData = $composerData;

        $addPackage = new AddPackage('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerData, $addPackage->modify($composerData));
    }

    public function testAddPackageToUnknownSection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "require", "require-dev". Got: "require_dev"');
        new AddPackage('vendor1/package1', '^1.0', 'require_dev');
    }
}
