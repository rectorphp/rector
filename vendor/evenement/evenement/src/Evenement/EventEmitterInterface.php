<?php

declare (strict_types=1);
/*
 * This file is part of Evenement.
 *
 * (c) Igor Wiedler <igor@wiedler.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211123\Evenement;

interface EventEmitterInterface
{
    /**
     * @param callable $listener
     */
    public function on($event, $listener);
    /**
     * @param callable $listener
     */
    public function once($event, $listener);
    /**
     * @param callable $listener
     */
    public function removeListener($event, $listener);
    public function removeAllListeners($event = null);
    public function listeners($event = null);
    /**
     * @param mixed[] $arguments
     */
    public function emit($event, $arguments = []);
}
