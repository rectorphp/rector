<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Tracy;

/**
 * Debugger for outputs.
 */
final class OutputDebugger
{
    private const BOM = "﻿";
    /** @var array of [file, line, output, stack] */
    private $list = [];
    public static function enable() : void
    {
        $me = new static();
        $me->start();
    }
    public function start() : void
    {
        foreach (\get_included_files() as $file) {
            if (\fread(\fopen($file, 'r'), 3) === self::BOM) {
                $this->list[] = [$file, 1, self::BOM];
            }
        }
        \ob_start([$this, 'handler'], 1);
    }
    /** @internal */
    public function handler(string $s, int $phase) : ?string
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        if (isset($trace[0]['file'], $trace[0]['line'])) {
            $stack = $trace;
            unset($stack[0]['line'], $stack[0]['args']);
            $i = \count($this->list);
            if ($i && $this->list[$i - 1][3] === $stack) {
                $this->list[$i - 1][2] .= $s;
            } else {
                $this->list[] = [$trace[0]['file'], $trace[0]['line'], $s, $stack];
            }
        }
        return $phase === \PHP_OUTPUT_HANDLER_FINAL ? $this->renderHtml() : null;
    }
    private function renderHtml() : string
    {
        $res = '<style>code, pre {white-space:nowrap} a {text-decoration:none} pre {color:gray;display:inline} big {color:red}</style><code>';
        foreach ($this->list as $item) {
            $stack = [];
            foreach (\array_slice($item[3], 1) as $t) {
                $t += ['class' => '', 'type' => '', 'function' => ''];
                $stack[] = "{$t['class']}{$t['type']}{$t['function']}()" . (isset($t['file'], $t['line']) ? ' in ' . \basename($t['file']) . ":{$t['line']}" : '');
            }
            $res .= '<span title="' . \RectorPrefix20211020\Tracy\Helpers::escapeHtml(\implode("\n", $stack)) . '">' . \RectorPrefix20211020\Tracy\Helpers::editorLink($item[0], $item[1]) . ' ' . \str_replace(self::BOM, '<big>BOM</big>', \RectorPrefix20211020\Tracy\Dumper::toHtml($item[2])) . "</span><br>\n";
        }
        return $res . '</code>';
    }
}
