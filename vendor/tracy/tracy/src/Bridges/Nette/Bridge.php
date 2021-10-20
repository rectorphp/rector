<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Tracy\Bridges\Nette;

use RectorPrefix20211020\Latte;
use RectorPrefix20211020\Nette;
use RectorPrefix20211020\Tracy;
use RectorPrefix20211020\Tracy\BlueScreen;
use RectorPrefix20211020\Tracy\Helpers;
/**
 * Bridge for NEON & Latte.
 */
class Bridge
{
    public static function initialize() : void
    {
        $blueScreen = \RectorPrefix20211020\Tracy\Debugger::getBlueScreen();
        $blueScreen->addPanel([self::class, 'renderLatteError']);
        $blueScreen->addAction([self::class, 'renderLatteUnknownMacro']);
        $blueScreen->addAction([self::class, 'renderMemberAccessException']);
        $blueScreen->addPanel([self::class, 'renderNeonError']);
    }
    /**
     * @param \Throwable|null $e
     */
    public static function renderLatteError($e) : ?array
    {
        if ($e instanceof \RectorPrefix20211020\Latte\CompileException && $e->sourceName) {
            return ['tab' => 'Template', 'panel' => (\preg_match('#\\n|\\?#', $e->sourceName) ? '' : '<p>' . (@\is_file($e->sourceName) ? '<b>File:</b> ' . \RectorPrefix20211020\Tracy\Helpers::editorLink($e->sourceName, $e->sourceLine) : '<b>' . \htmlspecialchars($e->sourceName . ($e->sourceLine ? ':' . $e->sourceLine : '')) . '</b>') . '</p>') . '<pre class=code><div>' . \RectorPrefix20211020\Tracy\BlueScreen::highlightLine(\htmlspecialchars($e->sourceCode, \ENT_IGNORE, 'UTF-8'), $e->sourceLine) . '</div></pre>'];
        } elseif ($e && \strpos($file = $e->getFile(), '.latte--')) {
            $lines = \file($file);
            if (\preg_match('#// source: (\\S+\\.latte)#', $lines[1], $m) && @\is_file($m[1])) {
                // @ - may trigger error
                $templateFile = $m[1];
                $templateLine = $e->getLine() && \preg_match('#/\\* line (\\d+) \\*/#', $lines[$e->getLine() - 1], $m) ? (int) $m[1] : 0;
                return ['tab' => 'Template', 'panel' => '<p><b>File:</b> ' . \RectorPrefix20211020\Tracy\Helpers::editorLink($templateFile, $templateLine) . '</p>' . ($templateLine === null ? '' : \RectorPrefix20211020\Tracy\BlueScreen::highlightFile($templateFile, $templateLine))];
            }
        }
        return null;
    }
    /**
     * @param \Throwable|null $e
     */
    public static function renderLatteUnknownMacro($e) : ?array
    {
        if ($e instanceof \RectorPrefix20211020\Latte\CompileException && $e->sourceName && @\is_file($e->sourceName) && (\preg_match('#Unknown macro (\\{\\w+)\\}, did you mean (\\{\\w+)\\}\\?#A', $e->getMessage(), $m) || \preg_match('#Unknown attribute (n:\\w+), did you mean (n:\\w+)\\?#A', $e->getMessage(), $m))) {
            return ['link' => \RectorPrefix20211020\Tracy\Helpers::editorUri($e->sourceName, $e->sourceLine, 'fix', $m[1], $m[2]), 'label' => 'fix it'];
        }
        return null;
    }
    /**
     * @param \Throwable|null $e
     */
    public static function renderMemberAccessException($e) : ?array
    {
        if (!$e instanceof \RectorPrefix20211020\Nette\MemberAccessException && !$e instanceof \LogicException) {
            return null;
        }
        $loc = $e->getTrace()[$e instanceof \RectorPrefix20211020\Nette\MemberAccessException ? 1 : 0];
        if (\preg_match('#Cannot (?:read|write to) an undeclared property .+::\\$(\\w+), did you mean \\$(\\w+)\\?#A', $e->getMessage(), $m)) {
            return ['link' => \RectorPrefix20211020\Tracy\Helpers::editorUri($loc['file'], $loc['line'], 'fix', '->' . $m[1], '->' . $m[2]), 'label' => 'fix it'];
        } elseif (\preg_match('#Call to undefined (static )?method .+::(\\w+)\\(\\), did you mean (\\w+)\\(\\)?#A', $e->getMessage(), $m)) {
            $operator = $m[1] ? '::' : '->';
            return ['link' => \RectorPrefix20211020\Tracy\Helpers::editorUri($loc['file'], $loc['line'], 'fix', $operator . $m[2] . '(', $operator . $m[3] . '('), 'label' => 'fix it'];
        }
        return null;
    }
    /**
     * @param \Throwable|null $e
     */
    public static function renderNeonError($e) : ?array
    {
        if ($e instanceof \RectorPrefix20211020\Nette\Neon\Exception && \preg_match('#line (\\d+)#', $e->getMessage(), $m) && ($trace = \RectorPrefix20211020\Tracy\Helpers::findTrace($e->getTrace(), [\RectorPrefix20211020\Nette\Neon\Decoder::class, 'decode']))) {
            return ['tab' => 'NEON', 'panel' => ($trace2 = \RectorPrefix20211020\Tracy\Helpers::findTrace($e->getTrace(), [\RectorPrefix20211020\Nette\DI\Config\Adapters\NeonAdapter::class, 'load'])) ? '<p><b>File:</b> ' . \RectorPrefix20211020\Tracy\Helpers::editorLink($trace2['args'][0], (int) $m[1]) . '</p>' . self::highlightNeon(\file_get_contents($trace2['args'][0]), (int) $m[1]) : self::highlightNeon($trace['args'][0], (int) $m[1])];
        }
        return null;
    }
    private static function highlightNeon(string $code, int $line) : string
    {
        $code = \htmlspecialchars($code, \ENT_IGNORE, 'UTF-8');
        $code = \str_replace(' ', "<span class='tracy-dump-whitespace'>·</span>", $code);
        $code = \str_replace("\t", "<span class='tracy-dump-whitespace'>→   </span>", $code);
        return '<pre class=code><div>' . \RectorPrefix20211020\Tracy\BlueScreen::highlightLine($code, $line) . '</div></pre>';
    }
}
