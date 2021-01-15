<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Rector\ComposerModifier;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequireDev;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackageAndVersion;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ComposerModifierTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
    }

    public function testRefactorWithOneAddedPackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([new AddPackageToRequire('vendor1/package3', '^3.0')]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithOneAddedAndOneRemovedPackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new AddPackageToRequire('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package1'),
        ]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithAddedAndRemovedSamePackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new AddPackageToRequire('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package3'),
        ]);

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
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithRemovedAndAddedBackSamePackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new RemovePackage('vendor1/package3'),
            new AddPackageToRequire('vendor1/package3', '^3.0'),
        ]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithMovedAndChangedPackages(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
            new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package3' => '~3.0.0',
            'vendor2/package1' => '^3.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithMultipleConfiguration(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([new MovePackageToRequireDev('vendor1/package1')]);
        $composerModifier->configure([new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0')]);
        $composerModifier->configure([new ChangePackageVersion('vendor1/package3', '~3.0.0')]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package3' => '~3.0.0',
            'vendor2/package1' => '^3.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithConfigurationAndReconfigurationAndConfiguration(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([new MovePackageToRequireDev('vendor1/package1')]);
        $composerModifier->reconfigure([new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0')]);
        $composerModifier->configure([new ChangePackageVersion('vendor1/package3', '~3.0.0')]);

        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package3' => '~3.0.0',
            'vendor2/package1' => '^3.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testRefactorWithMovedAndChangedPackagesWithSortPackages(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
            new ReplacePackageAndVersion('vendor1/package2', 'vendor1/package0', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $composerJson = new ComposerJson();
        $composerJson->setConfig([
            'sort-packages' => true,
        ]);
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
            'vendor1/package3' => '^3.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setConfig([
            'sort-packages' => true,
        ]);
        $expectedComposerJson->setRequire([
            'vendor1/package0' => '^3.0',
            'vendor1/package3' => '~3.0.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
        ]);
        $composerModifier->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testFilePath(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $this->assertSame(getcwd() . '/composer.json', $composerModifier->getFilePath());

        $composerModifier->setFilePath('test/composer.json');
        $this->assertSame('test/composer.json', $composerModifier->getFilePath());
    }

    public function testCommand(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $this->assertSame('composer update', $composerModifier->getCommand());
    }
}
