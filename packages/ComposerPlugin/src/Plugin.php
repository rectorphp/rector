<?php declare(strict_types=1);

namespace Rector\ComposerPlugin;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Sources:
 * - https://getcomposer.org/doc/articles/plugins.md
 * - https://www.sitepoint.com/drunk-with-the-power-of-composer-plugins/
 */
final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::PRE_PACKAGE_UPDATE => 'callThis',
            PackageEvents::PRE_PACKAGE_INSTALL => 'callThis',
        ];
    }

    public function callThis(PackageEvent $packageEvent)
    {
        foreach ($packageEvent->getOperations() as $operation) {
            if ($operation instanceof UpdateOperation) {
                dump($operation->getInitialPackage()->getName());
                dump($operation->getInitialPackage()->getVersion());
                dump($operation->getTargetPackage()->getVersion());
            } elseif ($operation instanceof InstallOperation) {
                dump($operation->getPackage()->getName());
                dump($operation->getPackage()->getVersion());
            }
        }
    }
}
