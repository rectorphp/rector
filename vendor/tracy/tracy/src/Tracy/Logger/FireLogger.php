<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202208\Tracy;

/**
 * FireLogger console logger.
 *
 * @see http://firelogger.binaryage.com
 * @see https://chrome.google.com/webstore/detail/firelogger-for-chrome/hmagilfopmdjkeomnjpchokglfdfjfeh
 */
class FireLogger implements ILogger
{
    /** @var int  */
    public $maxDepth = 3;
    /** @var int  */
    public $maxLength = 150;
    /** @var array  */
    private $payload = ['logs' => []];
    /**
     * Sends message to FireLogger console.
     * @param  mixed  $message
     */
    public function log($message, $level = self::DEBUG) : bool
    {
        if (!isset($_SERVER['HTTP_X_FIRELOGGER']) || \headers_sent()) {
            return \false;
        }
        $item = ['name' => 'PHP', 'level' => $level, 'order' => \count($this->payload['logs']), 'time' => \str_pad(\number_format((\microtime(\true) - Debugger::$time) * 1000, 1, '.', ' '), 8, '0', \STR_PAD_LEFT) . ' ms', 'template' => '', 'message' => '', 'style' => 'background:#767ab6'];
        $args = \func_get_args();
        if (isset($args[0]) && \is_string($args[0])) {
            $item['template'] = \array_shift($args);
        }
        if (isset($args[0]) && $args[0] instanceof \Throwable) {
            $e = \array_shift($args);
            $trace = $e->getTrace();
            if (isset($trace[0]['class']) && $trace[0]['class'] === Debugger::class && ($trace[0]['function'] === 'shutdownHandler' || $trace[0]['function'] === 'errorHandler')) {
                unset($trace[0]);
            }
            $file = \str_replace(\dirname($e->getFile(), 3), "…", $e->getFile());
            $item['template'] = ($e instanceof \ErrorException ? '' : Helpers::getClass($e) . ': ') . $e->getMessage() . ($e->getCode() ? ' #' . $e->getCode() : '') . ' in ' . $file . ':' . $e->getLine();
            $item['pathname'] = $e->getFile();
            $item['lineno'] = $e->getLine();
        } else {
            $trace = \debug_backtrace();
            if (isset($trace[1]['class']) && $trace[1]['class'] === Debugger::class && $trace[1]['function'] === 'fireLog') {
                unset($trace[0]);
            }
            foreach ($trace as $frame) {
                if (isset($frame['file']) && \is_file($frame['file'])) {
                    $item['pathname'] = $frame['file'];
                    $item['lineno'] = $frame['line'];
                    break;
                }
            }
        }
        $item['exc_info'] = ['', '', []];
        $item['exc_frames'] = [];
        foreach ($trace as $frame) {
            $frame += ['file' => null, 'line' => null, 'class' => null, 'type' => null, 'function' => null, 'object' => null, 'args' => null];
            $item['exc_info'][2][] = [$frame['file'], $frame['line'], "{$frame['class']}{$frame['type']}{$frame['function']}", $frame['object']];
            $item['exc_frames'][] = $frame['args'];
        }
        if (isset($args[0]) && \in_array($args[0], [self::DEBUG, self::INFO, self::WARNING, self::ERROR, self::CRITICAL], \true)) {
            $item['level'] = \array_shift($args);
        }
        $item['args'] = $args;
        $this->payload['logs'][] = $this->jsonDump($item, -1);
        foreach (\str_split(\base64_encode(\json_encode($this->payload, \JSON_INVALID_UTF8_SUBSTITUTE)), 4990) as $k => $v) {
            \header("FireLogger-de11e-{$k}: {$v}");
        }
        return \true;
    }
    /**
     * Dump implementation for JSON.
     * @param  mixed  $var
     * @return array|int|float|bool|string|null
     */
    private function jsonDump(&$var, int $level = 0)
    {
        if (\is_bool($var) || $var === null || \is_int($var) || \is_float($var)) {
            return $var;
        } elseif (\is_string($var)) {
            $var = Helpers::encodeString($var, $this->maxLength);
            return \htmlspecialchars_decode(\strip_tags($var));
        } elseif (\is_array($var)) {
            static $marker;
            if ($marker === null) {
                $marker = \uniqid("\x00", \true);
            }
            if (isset($var[$marker])) {
                return "…RECURSION…";
            } elseif ($level < $this->maxDepth || !$this->maxDepth) {
                $var[$marker] = \true;
                $res = [];
                foreach ($var as $k => &$v) {
                    if ($k !== $marker) {
                        $res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
                    }
                }
                unset($var[$marker]);
                return $res;
            } else {
                return " … ";
            }
        } elseif (\is_object($var)) {
            $arr = (array) $var;
            static $list = [];
            if (\in_array($var, $list, \true)) {
                return "…RECURSION…";
            } elseif ($level < $this->maxDepth || !$this->maxDepth) {
                $list[] = $var;
                $res = ["\x00" => '(object) ' . Helpers::getClass($var)];
                foreach ($arr as $k => &$v) {
                    if (isset($k[0]) && $k[0] === "\x00") {
                        $k = \substr($k, \strrpos($k, "\x00") + 1);
                    }
                    $res[$this->jsonDump($k)] = $this->jsonDump($v, $level + 1);
                }
                \array_pop($list);
                return $res;
            } else {
                return " … ";
            }
        } elseif (\is_resource($var)) {
            return 'resource ' . \get_resource_type($var);
        } else {
            return 'unknown type';
        }
    }
}
