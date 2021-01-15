<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class ReplacePackageTest extends TestCase
{
    public function testReplaceNonExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);
        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $replacePackage = new ReplacePackage('vendor1/package3', 'vendor1/package4', '^3.0');
        $replacePackage->refactor($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testReplaceExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package3' => '^3.0',
            'vendor1/package2' => '^2.0',
        ]);

        $replacePackage = new ReplacePackage('vendor1/package1', 'vendor1/package3', '^3.0');
        $replacePackage->refactor($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testReplaceExistingDevPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $composerJson->setRequireDev([
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package3' => '^3.0',
        ]);

        $replacePackage = new ReplacePackage('vendor1/package2', 'vendor1/package3', '^3.0');
        $replacePackage->refactor($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }
}
