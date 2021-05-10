<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io) : void
    {
    }
    public function deactivate(Composer $composer, IOInterface $io) : void
    {
    }
    public function uninstall(Composer $composer, IOInterface $io) : void
    {
    }
    public function process(Event $event) : void
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
        return [ScriptEvents::POST_INSTALL_CMD => 'process', ScriptEvents::POST_UPDATE_CMD => 'process'];
    }
}
