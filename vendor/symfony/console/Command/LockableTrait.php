<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\Console\Command;

use RectorPrefix20220501\Symfony\Component\Console\Exception\LogicException;
use RectorPrefix20220501\Symfony\Component\Lock\LockFactory;
use RectorPrefix20220501\Symfony\Component\Lock\Store\FlockStore;
use RectorPrefix20220501\Symfony\Component\Lock\Store\SemaphoreStore;
/**
 * Basic lock feature for commands.
 *
 * @author Geoffrey Brier <geoffrey.brier@gmail.com>
 */
trait LockableTrait
{
    private $lock = null;
    /**
     * Locks a command.
     */
    private function lock(string $name = null, bool $blocking = \false) : bool
    {
        if (!\class_exists(\RectorPrefix20220501\Symfony\Component\Lock\Store\SemaphoreStore::class)) {
            throw new \RectorPrefix20220501\Symfony\Component\Console\Exception\LogicException('To enable the locking feature you must install the symfony/lock component.');
        }
        if (null !== $this->lock) {
            throw new \RectorPrefix20220501\Symfony\Component\Console\Exception\LogicException('A lock is already in place.');
        }
        if (\RectorPrefix20220501\Symfony\Component\Lock\Store\SemaphoreStore::isSupported()) {
            $store = new \RectorPrefix20220501\Symfony\Component\Lock\Store\SemaphoreStore();
        } else {
            $store = new \RectorPrefix20220501\Symfony\Component\Lock\Store\FlockStore();
        }
        $this->lock = (new \RectorPrefix20220501\Symfony\Component\Lock\LockFactory($store))->createLock($name ?: $this->getName());
        if (!$this->lock->acquire($blocking)) {
            $this->lock = null;
            return \false;
        }
        return \true;
    }
    /**
     * Releases the command lock if there is one.
     */
    private function release()
    {
        if ($this->lock) {
            $this->lock->release();
            $this->lock = null;
        }
    }
}
