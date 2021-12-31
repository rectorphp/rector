<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211231\Tracy;

class FileSession implements \RectorPrefix20211231\Tracy\SessionStorage
{
    private const FILE_PREFIX = 'tracy-';
    private const COOKIE_LIFETIME = 31557600;
    /** @var string */
    public $cookieName = 'tracy-session';
    /** @var float probability that the clean() routine is started */
    public $gcProbability = 0.001;
    /** @var string */
    private $dir;
    /** @var resource */
    private $file;
    /** @var array */
    private $data = [];
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }
    public function isAvailable() : bool
    {
        if (!$this->file) {
            $this->open();
        }
        return \true;
    }
    private function open() : void
    {
        $id = $_COOKIE[$this->cookieName] ?? null;
        if (!\is_string($id) || !\preg_match('#^\\w{10}\\z#i', $id) || !($file = @\fopen($path = $this->dir . '/' . self::FILE_PREFIX . $id, 'r+'))) {
            $id = \RectorPrefix20211231\Tracy\Helpers::createId();
            \setcookie($this->cookieName, $id, \time() + self::COOKIE_LIFETIME, '/', '', \false, \true);
            $file = @\fopen($path = $this->dir . '/' . self::FILE_PREFIX . $id, 'c+');
            // intentionally @
            if ($file === \false) {
                throw new \RuntimeException("Unable to create file '{$path}'. " . \error_get_last()['message']);
            }
        }
        if (!@\flock($file, \LOCK_EX)) {
            // intentionally @
            throw new \RuntimeException("Unable to acquire exclusive lock on '{$path}'. ", \error_get_last()['message']);
        }
        $this->file = $file;
        $this->data = @\unserialize(\stream_get_contents($this->file)) ?: [];
        // @ - file may be empty
        if (\mt_rand() / \mt_getrandmax() < $this->gcProbability) {
            $this->clean();
        }
    }
    public function &getData() : array
    {
        return $this->data;
    }
    public function clean() : void
    {
        $old = \strtotime('-1 week');
        foreach (\glob($this->dir . '/' . self::FILE_PREFIX . '*') as $file) {
            if (\filemtime($file) < $old) {
                \unlink($file);
            }
        }
    }
    public function __destruct()
    {
        if (!$this->file) {
            return;
        }
        \ftruncate($this->file, 0);
        \fseek($this->file, 0);
        \fwrite($this->file, \serialize($this->data));
        \fclose($this->file);
        $this->file = null;
    }
}
