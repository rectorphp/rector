<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy;

use ErrorException;
/**
 * @internal
 */
final class ProductionStrategy
{
    public function initialize() : void
    {
        if (!\function_exists('ini_set') && (\ini_get('display_errors') && \ini_get('display_errors') !== 'stderr')) {
            Debugger::exceptionHandler(new \RuntimeException("Unable to set 'display_errors' because function ini_set() is disabled."));
        }
    }
    public function handleException(\Throwable $exception, bool $firstTime) : void
    {
        try {
            Debugger::log($exception, Debugger::EXCEPTION);
        } catch (\Throwable $e) {
        }
        if (!$firstTime) {
            // nothing
        } elseif (Helpers::isHtmlMode()) {
            if (!\headers_sent()) {
                \header('Content-Type: text/html; charset=UTF-8');
            }
            (function ($logged) {
                return require Debugger::$errorTemplate ?: __DIR__ . '/assets/error.500.phtml';
            })(empty($e));
        } elseif (Helpers::isCli()) {
            // @ triggers E_NOTICE when strerr is closed since PHP 7.4
            @\fwrite(\STDERR, "ERROR: {$exception->getMessage()}\n" . (isset($e) ? 'Unable to log error. You may try enable debug mode to inspect the problem.' : 'Check log to see more info.') . "\n");
        }
    }
    public function handleError(int $severity, string $message, string $file, int $line) : void
    {
        if ($severity & Debugger::$logSeverity) {
            $err = new ErrorException($message, 0, $severity, $file, $line);
            Helpers::improveException($err);
        } else {
            $err = 'PHP ' . Helpers::errorTypeToString($severity) . ': ' . Helpers::improveError($message) . " in {$file}:{$line}";
        }
        try {
            Debugger::log($err, Debugger::ERROR);
        } catch (\Throwable $e) {
        }
    }
    public function sendAssets() : bool
    {
        return \false;
    }
    public function renderLoader() : void
    {
    }
    public function renderBar() : void
    {
    }
}
