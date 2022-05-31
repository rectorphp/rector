<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220531\Tracy;

use ErrorException;
/**
 * @internal
 */
final class DevelopmentStrategy
{
    /** @var Bar */
    private $bar;
    /** @var BlueScreen */
    private $blueScreen;
    /** @var DeferredContent */
    private $defer;
    public function __construct(\RectorPrefix20220531\Tracy\Bar $bar, \RectorPrefix20220531\Tracy\BlueScreen $blueScreen, \RectorPrefix20220531\Tracy\DeferredContent $defer)
    {
        $this->bar = $bar;
        $this->blueScreen = $blueScreen;
        $this->defer = $defer;
    }
    public function initialize() : void
    {
    }
    public function handleException(\Throwable $exception, bool $firstTime) : void
    {
        if (\RectorPrefix20220531\Tracy\Helpers::isAjax() && $this->defer->isAvailable()) {
            $this->blueScreen->renderToAjax($exception, $this->defer);
        } elseif ($firstTime && \RectorPrefix20220531\Tracy\Helpers::isHtmlMode()) {
            $this->blueScreen->render($exception);
        } else {
            \RectorPrefix20220531\Tracy\Debugger::fireLog($exception);
            $this->renderExceptionCli($exception);
        }
    }
    private function renderExceptionCli(\Throwable $exception) : void
    {
        try {
            $logFile = \RectorPrefix20220531\Tracy\Debugger::log($exception, \RectorPrefix20220531\Tracy\Debugger::EXCEPTION);
        } catch (\Throwable $e) {
            echo "{$exception}\nTracy is unable to log error: {$e->getMessage()}\n";
            return;
        }
        if ($logFile && !\headers_sent()) {
            \header("X-Tracy-Error-Log: {$logFile}", \false);
        }
        if (\RectorPrefix20220531\Tracy\Helpers::detectColors()) {
            echo "\n\n" . $this->blueScreen->highlightPhpCli($exception->getFile(), $exception->getLine()) . "\n";
        }
        echo "{$exception}\n" . ($logFile ? "\n(stored in {$logFile})\n" : '');
        if ($logFile && \RectorPrefix20220531\Tracy\Debugger::$browser) {
            \exec(\RectorPrefix20220531\Tracy\Debugger::$browser . ' ' . \escapeshellarg(\strtr($logFile, \RectorPrefix20220531\Tracy\Debugger::$editorMapping)));
        }
    }
    public function handleError(int $severity, string $message, string $file, int $line, array $context = null) : void
    {
        if (\function_exists('ini_set')) {
            $oldDisplay = \ini_set('display_errors', '1');
        }
        if ((\is_bool(\RectorPrefix20220531\Tracy\Debugger::$strictMode) ? \RectorPrefix20220531\Tracy\Debugger::$strictMode : \RectorPrefix20220531\Tracy\Debugger::$strictMode & $severity) && !isset($_GET['_tracy_skip_error'])) {
            $e = new \ErrorException($message, 0, $severity, $file, $line);
            $e->context = $context;
            $e->skippable = \true;
            \RectorPrefix20220531\Tracy\Debugger::exceptionHandler($e);
            exit(255);
        }
        $message = 'PHP ' . \RectorPrefix20220531\Tracy\Helpers::errorTypeToString($severity) . ': ' . \RectorPrefix20220531\Tracy\Helpers::improveError($message, (array) $context);
        $count =& $this->bar->getPanel('Tracy:errors')->data["{$file}|{$line}|{$message}"];
        if (!$count++) {
            // not repeated error
            \RectorPrefix20220531\Tracy\Debugger::fireLog(new \ErrorException($message, 0, $severity, $file, $line));
            if (!\RectorPrefix20220531\Tracy\Helpers::isHtmlMode() && !\RectorPrefix20220531\Tracy\Helpers::isAjax()) {
                echo "\n{$message} in {$file} on line {$line}\n";
            }
        }
        if (\function_exists('ini_set')) {
            \ini_set('display_errors', $oldDisplay);
        }
    }
    public function sendAssets() : bool
    {
        return $this->defer->sendAssets();
    }
    public function renderLoader() : void
    {
        $this->bar->renderLoader($this->defer);
    }
    public function renderBar() : void
    {
        if (\function_exists('ini_set')) {
            \ini_set('display_errors', '1');
        }
        $this->bar->render($this->defer);
    }
}
