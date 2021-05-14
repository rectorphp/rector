<?php

declare (strict_types=1);
namespace Rector\RectorInstaller\Tests;

use RectorPrefix20210514\Composer\Installer\InstallationManager;
use RectorPrefix20210514\Composer\IO\IOInterface;
use RectorPrefix20210514\Composer\Package\PackageInterface;
use RectorPrefix20210514\Composer\Repository\InstalledRepositoryInterface;
use RectorPrefix20210514\PHPUnit\Framework\TestCase;
use RectorPrefix20210514\Prophecy\Argument;
use RectorPrefix20210514\Prophecy\PhpUnit\ProphecyTrait;
use RectorPrefix20210514\Prophecy\Prophecy\ObjectProphecy;
use Rector\RectorInstaller\Filesystem;
use Rector\RectorInstaller\PluginInstaller;
/**
 * @covers PluginInstaller
 */
final class PluginInstallerTest extends \RectorPrefix20210514\PHPUnit\Framework\TestCase
{
    use ProphecyTrait;
    /**
     * @var string
     */
    private const FILE_HASH = 'hash';
    /**
     * @var PluginInstaller
     */
    private $pluginInstaller;
    /**
     * @var InstalledRepositoryInterface|ObjectProphecy
     */
    private $localRepository;
    /**
     * @var IOInterface|ObjectProphecy
     */
    private $io;
    /**
     * @var InstallationManager|ObjectProphecy
     */
    private $installationManager;
    /**
     * @var ObjectProphecy|Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $configurationFile;
    /**
     * @var \Composer\Util\Filesystem|ObjectProphecy
     */
    private $composerFilesystem;
    protected function setUp() : void
    {
        $this->configurationFile = __FILE__;
        $this->composerFilesystem = $this->prophesize(\RectorPrefix20210514\Composer\Util\Filesystem::class);
        $this->filesystem = $this->prophesize(\Rector\RectorInstaller\Filesystem::class);
        $this->filesystem->isFile($this->configurationFile)->shouldBeCalledOnce()->willReturn(\true);
        $this->filesystem->hashFile($this->configurationFile)->willReturn(self::FILE_HASH);
        $this->localRepository = $this->prophesize(\RectorPrefix20210514\Composer\Repository\InstalledRepositoryInterface::class);
        $this->io = $this->prophesize(\RectorPrefix20210514\Composer\IO\IOInterface::class);
        $this->installationManager = $this->prophesize(\RectorPrefix20210514\Composer\Installer\InstallationManager::class);
        $this->pluginInstaller = new \Rector\RectorInstaller\PluginInstaller($this->filesystem->reveal(), $this->localRepository->reveal(), $this->io->reveal(), $this->installationManager->reveal(), $this->composerFilesystem->reveal(), $this->configurationFile);
    }
    public function testNoRectorPackagesInstalled() : void
    {
        $this->localRepository->getPackages()->willReturn([]);
        $this->filesystem->writeFile($this->configurationFile, \RectorPrefix20210514\Prophecy\Argument::any())->shouldNotBeCalled();
        $this->filesystem->hashEquals(self::FILE_HASH, \RectorPrefix20210514\Prophecy\Argument::any())->willReturn(\true);
        $this->pluginInstaller->install();
    }
    public function testPackagesInstalled() : void
    {
        $rectorExtensionPackage = $this->prophesize(\RectorPrefix20210514\Composer\Package\PackageInterface::class);
        $rectorExtensionPackage->getType()->willReturn(\Rector\RectorInstaller\PluginInstaller::RECTOR_EXTENSION_TYPE);
        $rectorExtensionPackage->getName()->willReturn('rector/doctrine');
        $rectorExtensionPackage->getFullPrettyVersion()->willReturn('rector/doctrine');
        $rectorExtensionPackage->getExtra()->willReturn([\Rector\RectorInstaller\PluginInstaller::RECTOR_EXTRA_KEY => ['includes' => ['config/config.php']]]);
        $nonRectorExtensionPackage = $this->prophesize(\RectorPrefix20210514\Composer\Package\PackageInterface::class);
        $nonRectorExtensionPackageWithExtra = $this->prophesize(\RectorPrefix20210514\Composer\Package\PackageInterface::class);
        $nonRectorExtensionPackageWithExtra->getType()->willReturn(null);
        $nonRectorExtensionPackageWithExtra->getName()->willReturn('rector/foo');
        $nonRectorExtensionPackageWithExtra->getFullPrettyVersion()->willReturn('rector/foo');
        $nonRectorExtensionPackageWithExtra->getExtra()->willReturn([\Rector\RectorInstaller\PluginInstaller::RECTOR_EXTRA_KEY => ['includes' => ['config/config.php']]]);
        $packages = [$rectorExtensionPackage, $nonRectorExtensionPackage, $nonRectorExtensionPackageWithExtra];
        $this->io->write('<info>rector/rector-installer:</info> Extensions installed')->shouldBeCalledOnce();
        $this->io->write(\sprintf('> <info>%s:</info> installed', 'rector/doctrine'))->shouldBeCalledOnce();
        $this->io->write(\sprintf('> <info>%s:</info> installed', 'rector/foo'))->shouldBeCalledOnce();
        $this->installationManager->getInstallPath($rectorExtensionPackage)->shouldBeCalledOnce();
        $this->installationManager->getInstallPath($nonRectorExtensionPackageWithExtra)->shouldBeCalledOnce();
        $this->filesystem->hashEquals(self::FILE_HASH, \RectorPrefix20210514\Prophecy\Argument::any())->willReturn(\false);
        $this->filesystem->writeFile($this->configurationFile, \RectorPrefix20210514\Prophecy\Argument::any())->shouldBeCalledOnce();
        $this->localRepository->getPackages()->willReturn($packages);
        $this->pluginInstaller->install();
    }
}
