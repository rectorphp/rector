<?php

namespace Rector\Composer\Tests\Modifier;

use Nette\Utils\Json;
use Rector\Composer\ValueObject\AddPackageToRequire;
use Rector\Composer\ValueObject\ReplacePackage;
use Rector\Composer\ValueObject\ChangePackageVersion;
use Rector\Composer\ValueObject\MovePackageToRequireDev;
use Rector\Composer\ValueObject\RemovePackage;
use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\HttpKernel\RectorKernel;
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
        $composerModifier->configure([
            new AddPackageToRequire('vendor1/package3', '^3.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithOneAddedAndOneRemovedPackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new AddPackageToRequire('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package1'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithAddedAndRemovedSamePackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new AddPackageToRequire('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package3'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithRemovedAndAddedBackSamePackage(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new RemovePackage('vendor1/package3'),
            new AddPackageToRequire('vendor1/package3', '^3.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithMovedAndChangedPackages(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
            new ReplacePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithMultipleConfiguration(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
        ]);
        $composerModifier->configure([
            new ReplacePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerModifier->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithConfigurationAndReconfigurationAndConfiguration(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
        ]);
        $composerModifier->reconfigure([
            new ReplacePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerModifier->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testRefactorWithMovedAndChangedPackagesWithSortPackages(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $composerModifier->configure([
            new MovePackageToRequireDev('vendor1/package1'),
            new ReplacePackage('vendor1/package2', 'vendor1/package0', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'config' => [
                'sort-packages' => true,
            ],
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'config' => [
                'sort-packages' => true,
            ],
            'require' => [
                'vendor1/package0' => '^3.0',
                'vendor1/package3' => '~3.0.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerModifier->modify($originalContent));
    }

    public function testFilePath(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $this->assertEquals(getcwd() . '/composer.json', $composerModifier->getFilePath());

        $composerModifier->filePath('test/composer.json');
        $this->assertEquals('test/composer.json', $composerModifier->getFilePath());
    }

    public function testCommand(): void
    {
        /** @var ComposerModifier $composerModifier */
        $composerModifier = $this->getService(ComposerModifier::class);
        $this->assertEquals('composer update', $composerModifier->getCommand());

        $composerModifier->command('composer update --prefer-stable');
        $this->assertEquals('composer update --prefer-stable', $composerModifier->getCommand());
    }
}
