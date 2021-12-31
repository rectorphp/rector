<?php

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Composer\XdebugHandler;

use RectorPrefix20211231\Psr\Log\LoggerInterface;
use RectorPrefix20211231\Psr\Log\LogLevel;
/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 * @internal
 */
class Status
{
    const ENV_RESTART = 'XDEBUG_HANDLER_RESTART';
    const CHECK = 'Check';
    const ERROR = 'Error';
    const INFO = 'Info';
    const NORESTART = 'NoRestart';
    const RESTART = 'Restart';
    const RESTARTING = 'Restarting';
    const RESTARTED = 'Restarted';
    /** @var bool */
    private $debug;
    /** @var string */
    private $envAllowXdebug;
    /** @var string|null */
    private $loaded;
    /** @var LoggerInterface|null */
    private $logger;
    /** @var bool */
    private $modeOff;
    /** @var float */
    private $time;
    /**
     * Constructor
     *
     * @param string $envAllowXdebug Prefixed _ALLOW_XDEBUG name
     * @param bool $debug Whether debug output is required
     */
    public function __construct($envAllowXdebug, $debug)
    {
        $start = \getenv(self::ENV_RESTART);
        \RectorPrefix20211231\Composer\XdebugHandler\Process::setEnv(self::ENV_RESTART);
        $this->time = \is_numeric($start) ? \round((\microtime(\true) - $start) * 1000) : 0;
        $this->envAllowXdebug = $envAllowXdebug;
        $this->debug = $debug && \defined('STDERR');
        $this->modeOff = \false;
    }
    /**
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(\RectorPrefix20211231\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Calls a handler method to report a message
     *
     * @param string $op The handler constant
     * @param null|string $data Data required by the handler
     *
     * @return void
     * @throws \InvalidArgumentException If $op is not known
     */
    public function report($op, $data)
    {
        if ($this->logger !== null || $this->debug) {
            $callable = array($this, 'report' . $op);
            if (!\is_callable($callable)) {
                throw new \InvalidArgumentException('Unknown op handler: ' . $op);
            }
            $params = $data !== null ? $data : array();
            \call_user_func_array($callable, array($params));
        }
    }
    /**
     * Outputs a status message
     *
     * @param string $text
     * @param string $level
     *
     * @return void
     */
    private function output($text, $level = null)
    {
        if ($this->logger !== null) {
            $this->logger->log($level !== null ? $level : \RectorPrefix20211231\Psr\Log\LogLevel::DEBUG, $text);
        }
        if ($this->debug) {
            \fwrite(\STDERR, \sprintf('xdebug-handler[%d] %s', \getmypid(), $text . \PHP_EOL));
        }
    }
    /**
     * @param string $loaded
     *
     * @return void
     */
    private function reportCheck($loaded)
    {
        list($version, $mode) = \explode('|', $loaded);
        if ($version !== '') {
            $this->loaded = '(' . $version . ')' . ($mode !== '' ? ' mode=' . $mode : '');
        }
        $this->modeOff = $mode === 'off';
        $this->output('Checking ' . $this->envAllowXdebug);
    }
    /**
     * @param string $error
     *
     * @return void
     */
    private function reportError($error)
    {
        $this->output(\sprintf('No restart (%s)', $error), \RectorPrefix20211231\Psr\Log\LogLevel::WARNING);
    }
    /**
     * @param string $info
     *
     * @return void
     */
    private function reportInfo($info)
    {
        $this->output($info);
    }
    /**
     * @return void
     */
    private function reportNoRestart()
    {
        $this->output($this->getLoadedMessage());
        if ($this->loaded !== null) {
            $text = \sprintf('No restart (%s)', $this->getEnvAllow());
            if (!(bool) \getenv($this->envAllowXdebug)) {
                $text .= ' Allowed by ' . ($this->modeOff ? 'mode' : 'application');
            }
            $this->output($text);
        }
    }
    /**
     * @return void
     */
    private function reportRestart()
    {
        $this->output($this->getLoadedMessage());
        \RectorPrefix20211231\Composer\XdebugHandler\Process::setEnv(self::ENV_RESTART, (string) \microtime(\true));
    }
    /**
     * @return void
     */
    private function reportRestarted()
    {
        $loaded = $this->getLoadedMessage();
        $text = \sprintf('Restarted (%d ms). %s', $this->time, $loaded);
        $level = $this->loaded !== null ? \RectorPrefix20211231\Psr\Log\LogLevel::WARNING : null;
        $this->output($text, $level);
    }
    /**
     * @param string $command
     *
     * @return void
     */
    private function reportRestarting($command)
    {
        $text = \sprintf('Process restarting (%s)', $this->getEnvAllow());
        $this->output($text);
        $text = 'Running ' . $command;
        $this->output($text);
    }
    /**
     * Returns the _ALLOW_XDEBUG environment variable as name=value
     *
     * @return string
     */
    private function getEnvAllow()
    {
        return $this->envAllowXdebug . '=' . \getenv($this->envAllowXdebug);
    }
    /**
     * Returns the Xdebug status and version
     *
     * @return string
     */
    private function getLoadedMessage()
    {
        $loaded = $this->loaded !== null ? \sprintf('loaded %s', $this->loaded) : 'not loaded';
        return 'The Xdebug extension is ' . $loaded;
    }
}
