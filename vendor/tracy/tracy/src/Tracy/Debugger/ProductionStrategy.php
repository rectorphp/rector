<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220501\Tracy;

use ErrorException;
/**
 * @internal
 */
final class ProductionStrategy
{
    public function initialize() : void
    {
        if (!\function_exists('ini_set') && (\ini_get('display_errors') && \ini_get('display_errors') !== 'stderr')) {
            \RectorPrefix20220501\Tracy\Debugger::exceptionHandler(new \RuntimeException("Unable to set 'display_errors' because function ini_set() is disabled."));
        }
    }
    public function handleException(\Throwable $exception, bool $firstTime) : void
    {
        try {
            \RectorPrefix20220501\Tracy\Debugger::log($exception, \RectorPrefix20220501\Tracy\Debugger::EXCEPTION);
        } catch (\Throwable $e) {
        }
        if (!$firstTime) {
            // nothing
        } elseif (\RectorPrefix20220501\Tracy\Helpers::isHtmlMode()) {
            if (!\headers_sent()) {
                \header('Content-Type: text/html; charset=UTF-8');
            }
            (function ($logged) use($exception) {
                require \RectorPrefix20220501\Tracy\Debugger::$errorTemplate ?: __DIR__ . '/assets/error.500.phtml';
            })(empty($e));
        } elseif (\RectorPrefix20220501\Tracy\Helpers::isCli()) {
            // @ triggers E_NOTICE when strerr is closed since PHP 7.4
            @\fwrite(\STDERR, "ERROR: {$exception->getMessage()}\n" . (isset($e) ? 'Unable to log error. You may try enable debug mode to inspect the problem.' : 'Check log to see more info.') . "\n");
        }
    }
    public function handleError(int $severity, string $message, string $file, int $line, array $context = null) : void
    {
        if ($severity & \RectorPrefix20220501\Tracy\Debugger::$logSeverity) {
            $err = new \ErrorException($message, 0, $severity, $file, $line);
            $err->context = $context;
            \RectorPrefix20220501\Tracy\Helpers::improveException($err);
        } else {
            $err = 'PHP ' . \RectorPrefix20220501\Tracy\Helpers::errorTypeToString($severity) . ': ' . \RectorPrefix20220501\Tracy\Helpers::improveError($message, (array) $context) . " in {$file}:{$line}";
        }
        try {
            \RectorPrefix20220501\Tracy\Debugger::log($err, \RectorPrefix20220501\Tracy\Debugger::ERROR);
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
