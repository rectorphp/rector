<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211231\Tracy\Bridges\Nette;

use RectorPrefix20211231\Latte;
use RectorPrefix20211231\Nette;
use RectorPrefix20211231\Tracy;
use RectorPrefix20211231\Tracy\BlueScreen;
use RectorPrefix20211231\Tracy\Helpers;
/**
 * Bridge for NEON & Latte.
 */
class Bridge
{
    public static function initialize() : void
    {
        $blueScreen = \RectorPrefix20211231\Tracy\Debugger::getBlueScreen();
        if (!\class_exists(\RectorPrefix20211231\Latte\Bridges\Tracy\BlueScreenPanel::class)) {
            $blueScreen->addPanel([self::class, 'renderLatteError']);
            $blueScreen->addAction([self::class, 'renderLatteUnknownMacro']);
            $blueScreen->addFileGenerator(function (string $file) {
                return \substr($file, -6) === '.latte' ? "{block content}\n\$END\$" : null;
            });
            \RectorPrefix20211231\Tracy\Debugger::addSourceMapper([self::class, 'mapLatteSourceCode']);
        }
        $blueScreen->addAction([self::class, 'renderMemberAccessException']);
        $blueScreen->addPanel([self::class, 'renderNeonError']);
    }
    public static function renderLatteError(?\Throwable $e) : ?array
    {
        if ($e instanceof \RectorPrefix20211231\Latte\CompileException && $e->sourceName) {
            return ['tab' => 'Template', 'panel' => (\preg_match('#\\n|\\?#', $e->sourceName) ? '' : '<p>' . (@\is_file($e->sourceName) ? '<b>File:</b> ' . \RectorPrefix20211231\Tracy\Helpers::editorLink($e->sourceName, $e->sourceLine) : '<b>' . \htmlspecialchars($e->sourceName . ($e->sourceLine ? ':' . $e->sourceLine : '')) . '</b>') . '</p>') . \RectorPrefix20211231\Tracy\BlueScreen::highlightFile($e->sourceCode, $e->sourceLine, 15, \false)];
        }
        return null;
    }
    public static function renderLatteUnknownMacro(?\Throwable $e) : ?array
    {
        if ($e instanceof \RectorPrefix20211231\Latte\CompileException && $e->sourceName && @\is_file($e->sourceName) && (\preg_match('#Unknown macro (\\{\\w+)\\}, did you mean (\\{\\w+)\\}\\?#A', $e->getMessage(), $m) || \preg_match('#Unknown attribute (n:\\w+), did you mean (n:\\w+)\\?#A', $e->getMessage(), $m))) {
            return ['link' => \RectorPrefix20211231\Tracy\Helpers::editorUri($e->sourceName, $e->sourceLine, 'fix', $m[1], $m[2]), 'label' => 'fix it'];
        }
        return null;
    }
    /** @return array{file: string, line: int, label: string, active: bool} */
    public static function mapLatteSourceCode(string $file, int $line) : ?array
    {
        if (!\strpos($file, '.latte--')) {
            return null;
        }
        $lines = \file($file);
        if (!\preg_match('#^/(?:\\*\\*|/) source: (\\S+\\.latte)#m', \implode('', \array_slice($lines, 0, 10)), $m) || !@\is_file($m[1])) {
            return null;
        }
        $file = $m[1];
        $line = $line && \preg_match('#/\\* line (\\d+) \\*/#', $lines[$line - 1], $m) ? (int) $m[1] : 0;
        return ['file' => $file, 'line' => $line, 'label' => 'Latte', 'active' => \true];
    }
    public static function renderMemberAccessException(?\Throwable $e) : ?array
    {
        if (!$e instanceof \RectorPrefix20211231\Nette\MemberAccessException && !$e instanceof \LogicException) {
            return null;
        }
        $loc = $e->getTrace()[$e instanceof \RectorPrefix20211231\Nette\MemberAccessException ? 1 : 0];
        if (!isset($loc['file'])) {
            return null;
        }
        $loc = \RectorPrefix20211231\Tracy\Debugger::mapSource($loc['file'], $loc['line']) ?? $loc;
        if (\preg_match('#Cannot (?:read|write to) an undeclared property .+::\\$(\\w+), did you mean \\$(\\w+)\\?#A', $e->getMessage(), $m)) {
            return ['link' => \RectorPrefix20211231\Tracy\Helpers::editorUri($loc['file'], $loc['line'], 'fix', '->' . $m[1], '->' . $m[2]), 'label' => 'fix it'];
        } elseif (\preg_match('#Call to undefined (static )?method .+::(\\w+)\\(\\), did you mean (\\w+)\\(\\)?#A', $e->getMessage(), $m)) {
            $operator = $m[1] ? '::' : '->';
            return ['link' => \RectorPrefix20211231\Tracy\Helpers::editorUri($loc['file'], $loc['line'], 'fix', $operator . $m[2] . '(', $operator . $m[3] . '('), 'label' => 'fix it'];
        }
        return null;
    }
    public static function renderNeonError(?\Throwable $e) : ?array
    {
        if (!$e instanceof \RectorPrefix20211231\Nette\Neon\Exception || !\preg_match('#line (\\d+)#', $e->getMessage(), $m)) {
            return null;
        } elseif ($trace = \RectorPrefix20211231\Tracy\Helpers::findTrace($e->getTrace(), [\RectorPrefix20211231\Nette\Neon\Decoder::class, 'decodeFile']) ?? \RectorPrefix20211231\Tracy\Helpers::findTrace($e->getTrace(), [\RectorPrefix20211231\Nette\DI\Config\Adapters\NeonAdapter::class, 'load'])) {
            $panel = '<p><b>File:</b> ' . \RectorPrefix20211231\Tracy\Helpers::editorLink($trace['args'][0], (int) $m[1]) . '</p>' . self::highlightNeon(\file_get_contents($trace['args'][0]), (int) $m[1]);
        } elseif ($trace = \RectorPrefix20211231\Tracy\Helpers::findTrace($e->getTrace(), [\RectorPrefix20211231\Nette\Neon\Decoder::class, 'decode'])) {
            $panel = self::highlightNeon($trace['args'][0], (int) $m[1]);
        }
        return isset($panel) ? ['tab' => 'NEON', 'panel' => $panel] : null;
    }
    private static function highlightNeon(string $code, int $line) : string
    {
        $code = \htmlspecialchars($code, \ENT_IGNORE, 'UTF-8');
        $code = \str_replace(' ', "<span class='tracy-dump-whitespace'>·</span>", $code);
        $code = \str_replace("\t", "<span class='tracy-dump-whitespace'>→   </span>", $code);
        return '<pre class=code><div>' . \RectorPrefix20211231\Tracy\BlueScreen::highlightLine($code, $line) . '</div></pre>';
    }
}
