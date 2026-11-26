<?php

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202611\Composer\XdebugHandler;

use RectorPrefix202611\Psr\Log\LoggerInterface;
use RectorPrefix202611\Psr\Log\LogLevel;
/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 * @internal
 */
class Status
{
    const ENV_RESTART = 'XDEBUG_HANDLER_RESTART';
    /** @var bool */
    private $active;
    /** @var bool */
    private $debug;
    /** @var int */
    private $ellapsed;
    /** @var string */
    private $envAllowXdebug;
    /** @var string|null */
    private $loaded;
    /** @var LoggerInterface|null */
    private $logger;
    /** @var bool */
    private $modeOff;
    /** @var int */
    private $pid;
    /**
     * @param string $envAllowXdebug Prefixed _ALLOW_XDEBUG name
     * @param bool $debug Whether debug output is required
     * @param string|null $version Xdebug version
     * @param string|null $mode Xdebug mode
     */
    public function __construct(string $envAllowXdebug, bool $debug, ?string $version, ?string $mode)
    {
        $start = getenv(self::ENV_RESTART);
        putenv(self::ENV_RESTART);
        $this->ellapsed = is_numeric($start) ? (int) round((microtime(\true) - $start) * 1000) : 0;
        $this->envAllowXdebug = $envAllowXdebug;
        $this->active = $this->debug = $debug && defined('STDERR');
        if ((string) $version !== '') {
            $this->loaded = '(' . $version . ')' . ((string) $mode !== '' ? ' xdebug.mode=' . $mode : '');
        }
        $this->modeOff = $mode === 'off';
        $this->pid = (int) getmypid();
    }
    /**
     * Activates status message output to a PSR3 logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
        $this->active = \true;
    }
    /**
     * Using and Checking status messages
     */
    public function reportCheck(string $class): void
    {
        if (!$this->active) {
            return;
        }
        if ($class !== XdebugHandler::class) {
            $this->output(sprintf('Using extending class (%s)', $class));
        }
        $this->output('Checking ' . $this->envAllowXdebug);
    }
    /**
     * Shows the loaded status and saves the current time
     */
    public function reportRequired(): void
    {
        if ($this->active) {
            $this->output($this->getLoadedMessage());
            // Set the timer environment variable
            putenv(self::ENV_RESTART . '=' . (string) microtime(\true));
        }
    }
    /**
     * Restarting status message
     *
     * @param list<string>|string $command
     */
    public function reportRestarting($command): void
    {
        if (!$this->active) {
            return;
        }
        $text = sprintf('Process restarting (%s)', $this->getEnvAllow());
        $this->output($text);
        if (is_array($command)) {
            $command = sprintf('[%s]', implode(', ', $command));
        }
        $text = 'Running: ' . $command;
        $this->output($text);
    }
    /**
     * Exit message
     */
    public function reportExit(int $exitCode): void
    {
        if ($this->active) {
            $this->output('Restarted process exited ' . $exitCode);
        }
    }
    /**
     * Restarted status message
     */
    public function reportRestarted(): void
    {
        if ($this->active) {
            $loaded = $this->getLoadedMessage();
            $text = sprintf('Restarted (%d ms). %s', $this->ellapsed, $loaded);
            $level = $this->loaded !== null ? LogLevel::WARNING : null;
            $this->output($text, $level);
        }
    }
    /**
     * No restart status message
     */
    public function reportNoRestart(): void
    {
        if (!$this->active) {
            return;
        }
        $this->output($this->getLoadedMessage());
        if ($this->loaded !== null) {
            $text = sprintf('No restart (%s)', $this->getEnvAllow());
            if (!(bool) getenv($this->envAllowXdebug)) {
                $text .= ' Allowed by ' . ($this->modeOff ? 'xdebug.mode' : 'application');
            }
            $this->output($text);
        }
    }
    /**
     * Existing restart settings message
     */
    public function reportExisting(): void
    {
        if ($this->active) {
            $this->output('Process called with existing restart settings');
        }
    }
    /**
     * Configuration error, logged as a warning
     */
    public function configError(string $error): void
    {
        if ($this->active) {
            $this->output(sprintf('No restart (%s)', $error), LogLevel::WARNING);
        }
    }
    /**
     * General info
     */
    public function info(string $info): void
    {
        if ($this->active) {
            $this->output($info);
        }
    }
    /**
     * General error, logged as a warning
     */
    public function error(string $error): void
    {
        if ($this->active) {
            $this->output($error, LogLevel::WARNING);
        }
    }
    /**
     * Outputs a status message
     */
    private function output(string $text, ?string $level = null): void
    {
        if ($this->logger !== null) {
            $this->logger->log($level !== null ? $level : LogLevel::DEBUG, $text);
        }
        if ($this->debug) {
            fwrite(\STDERR, sprintf('xdebug-handler[%d] %s', $this->pid, $text . \PHP_EOL));
        }
    }
    /**
     * Returns the _ALLOW_XDEBUG environment variable as name=value
     */
    private function getEnvAllow(): string
    {
        return $this->envAllowXdebug . '=' . getenv($this->envAllowXdebug);
    }
    /**
     * Returns the Xdebug status and version
     */
    private function getLoadedMessage(): string
    {
        $loaded = $this->loaded !== null ? sprintf('loaded %s', $this->loaded) : 'not loaded';
        return 'The Xdebug extension is ' . $loaded;
    }
}
