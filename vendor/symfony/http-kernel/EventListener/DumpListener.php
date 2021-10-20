<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\EventListener;

use RectorPrefix20211020\Symfony\Component\Console\ConsoleEvents;
use RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\ClonerInterface;
use RectorPrefix20211020\Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use RectorPrefix20211020\Symfony\Component\VarDumper\Server\Connection;
use RectorPrefix20211020\Symfony\Component\VarDumper\VarDumper;
/**
 * Configures dump() handler.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class DumpListener implements \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $cloner;
    private $dumper;
    private $connection;
    public function __construct(\RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\ClonerInterface $cloner, \RectorPrefix20211020\Symfony\Component\VarDumper\Dumper\DataDumperInterface $dumper, \RectorPrefix20211020\Symfony\Component\VarDumper\Server\Connection $connection = null)
    {
        $this->cloner = $cloner;
        $this->dumper = $dumper;
        $this->connection = $connection;
    }
    public function configure()
    {
        $cloner = $this->cloner;
        $dumper = $this->dumper;
        $connection = $this->connection;
        \RectorPrefix20211020\Symfony\Component\VarDumper\VarDumper::setHandler(static function ($var) use($cloner, $dumper, $connection) {
            $data = $cloner->cloneVar($var);
            if (!$connection || !$connection->write($data)) {
                $dumper->dump($data);
            }
        });
    }
    public static function getSubscribedEvents()
    {
        if (!\class_exists(\RectorPrefix20211020\Symfony\Component\Console\ConsoleEvents::class)) {
            return [];
        }
        // Register early to have a working dump() as early as possible
        return [\RectorPrefix20211020\Symfony\Component\Console\ConsoleEvents::COMMAND => ['configure', 1024]];
    }
}
