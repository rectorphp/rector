<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public function process(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();

        $repositoryManager = $composer->getRepositoryManager();
        $localRepository = $repositoryManager->getLocalRepository();

        $configurationFile = __DIR__ . '/GeneratedConfig.php';
        $pluginInstaller = new PluginInstaller(
            new LocalFilesystem(),
            $localRepository,
            $io,
            $installationManager,
            new \Composer\Util\Filesystem(),
            $configurationFile
        );

        $pluginInstaller->install();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'process',
            ScriptEvents::POST_UPDATE_CMD => 'process',
        ];
    }
}
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector\Fixture;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public function process(Event $event)
    {
        $io = $event->getIO();
        $composer = $event->getComposer();
        $installationManager = $composer->getInstallationManager();

        $repositoryManager = $composer->getRepositoryManager();
        $localRepository = $repositoryManager->getLocalRepository();

        $configurationFile = __DIR__ . '/GeneratedConfig.php';
        $pluginInstaller = new PluginInstaller(
            new LocalFilesystem(),
            $localRepository,
            $io,
            $installationManager,
            new \Composer\Util\Filesystem(),
            $configurationFile
        );

        $pluginInstaller->install();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'process',
            ScriptEvents::POST_UPDATE_CMD => 'process',
        ];
    }
}

?>