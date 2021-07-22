<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
final class Plugin implements \Composer\Plugin\PluginInterface, \Composer\EventDispatcher\EventSubscriberInterface
{
    /**
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function activate($composer, $io) : void
    {
    }
    /**
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function deactivate($composer, $io) : void
    {
    }
    /**
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     */
    public function uninstall($composer, $io) : void
    {
    }
    /**
     * @param \Composer\Script\Event $event
     */
    public function process($event) : void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();
        $repositoryManager = $composer->getRepositoryManager();
        $localRepository = $repositoryManager->getLocalRepository();
        $configurationFile = __DIR__ . '/GeneratedConfig.php';
        $pluginInstaller = new \Rector\RectorInstaller\PluginInstaller(new \Rector\RectorInstaller\LocalFilesystem(), $localRepository, $io, $installationManager, new \Composer\Util\Filesystem(), $configurationFile);
        $pluginInstaller->install();
    }
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents() : array
    {
        return [\Composer\Script\ScriptEvents::POST_INSTALL_CMD => 'process', \Composer\Script\ScriptEvents::POST_UPDATE_CMD => 'process'];
    }
}
