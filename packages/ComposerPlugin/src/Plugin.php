<?php declare(strict_types=1);

namespace Rector\ComposerPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

final class Plugin implements PluginInterface
{
    /**
     * Apply plugin modifications to Composer
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        var_dump(123);
//        var_dump($composer);
//        var_dump($io);
    }
}
