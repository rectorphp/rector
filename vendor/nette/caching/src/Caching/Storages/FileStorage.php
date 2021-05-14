<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Nette\Caching\Storages;

use RectorPrefix20210514\Nette;
use RectorPrefix20210514\Nette\Caching\Cache;
/**
 * Cache file storage.
 */
class FileStorage implements \RectorPrefix20210514\Nette\Caching\Storage
{
    use Nette\SmartObject;
    /**
     * Atomic thread safe logic:
     *
     * 1) reading: open(r+b), lock(SH), read
     *     - delete?: delete*, close
     * 2) deleting: delete*
     * 3) writing: open(r+b || wb), lock(EX), truncate*, write data, write meta, close
     *
     * delete* = try unlink, if fails (on NTFS) { lock(EX), truncate, close, unlink } else close (on ext3)
     */
    /** @internal cache file structure: meta-struct size + serialized meta-struct + data */
    private const META_HEADER_LEN = 6, META_TIME = 'time', META_SERIALIZED = 'serialized', META_EXPIRE = 'expire', META_DELTA = 'delta', META_ITEMS = 'di', META_CALLBACKS = 'callbacks';
    // array of callbacks (function, args)
    /** additional cache structure */
    private const FILE = 'file', HANDLE = 'handle';
    /** @var float  probability that the clean() routine is started */
    public static $gcProbability = 0.001;
    /** @deprecated */
    public static $useDirectories = \true;
    /** @var string */
    private $dir;
    /** @var Journal */
    private $journal;
    /** @var array */
    private $locks;
    public function __construct(string $dir, \RectorPrefix20210514\Nette\Caching\Storages\Journal $journal = null)
    {
        if (!\is_dir($dir)) {
            throw new \RectorPrefix20210514\Nette\DirectoryNotFoundException("Directory '{$dir}' not found.");
        }
        $this->dir = $dir;
        $this->journal = $journal;
        if (\mt_rand() / \mt_getrandmax() < static::$gcProbability) {
            $this->clean([]);
        }
    }
    public function read(string $key)
    {
        $meta = $this->readMetaAndLock($this->getCacheFile($key), \LOCK_SH);
        return $meta && $this->verify($meta) ? $this->readData($meta) : null;
    }
    /**
     * Verifies dependencies.
     */
    private function verify(array $meta) : bool
    {
        do {
            if (!empty($meta[self::META_DELTA])) {
                // meta[file] was added by readMetaAndLock()
                if (\filemtime($meta[self::FILE]) + $meta[self::META_DELTA] < \time()) {
                    break;
                }
                \touch($meta[self::FILE]);
            } elseif (!empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE] < \time()) {
                break;
            }
            if (!empty($meta[self::META_CALLBACKS]) && !\RectorPrefix20210514\Nette\Caching\Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
                break;
            }
            if (!empty($meta[self::META_ITEMS])) {
                foreach ($meta[self::META_ITEMS] as $depFile => $time) {
                    $m = $this->readMetaAndLock($depFile, \LOCK_SH);
                    if (($m[self::META_TIME] ?? null) !== $time || $m && !$this->verify($m)) {
                        break 2;
                    }
                }
            }
            return \true;
        } while (\false);
        $this->delete($meta[self::FILE], $meta[self::HANDLE]);
        // meta[handle] & meta[file] was added by readMetaAndLock()
        return \false;
    }
    public function lock(string $key) : void
    {
        $cacheFile = $this->getCacheFile($key);
        if (!\is_dir($dir = \dirname($cacheFile))) {
            @\mkdir($dir);
            // @ - directory may already exist
        }
        $handle = \fopen($cacheFile, 'c+b');
        if (!$handle) {
            return;
        }
        $this->locks[$key] = $handle;
        \flock($handle, \LOCK_EX);
    }
    public function write(string $key, $data, array $dp) : void
    {
        $meta = [self::META_TIME => \microtime()];
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::EXPIRATION])) {
            if (empty($dp[\RectorPrefix20210514\Nette\Caching\Cache::SLIDING])) {
                $meta[self::META_EXPIRE] = $dp[\RectorPrefix20210514\Nette\Caching\Cache::EXPIRATION] + \time();
                // absolute time
            } else {
                $meta[self::META_DELTA] = (int) $dp[\RectorPrefix20210514\Nette\Caching\Cache::EXPIRATION];
                // sliding time
            }
        }
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::ITEMS])) {
            foreach ($dp[\RectorPrefix20210514\Nette\Caching\Cache::ITEMS] as $item) {
                $depFile = $this->getCacheFile($item);
                $m = $this->readMetaAndLock($depFile, \LOCK_SH);
                $meta[self::META_ITEMS][$depFile] = $m[self::META_TIME] ?? null;
                unset($m);
            }
        }
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::CALLBACKS])) {
            $meta[self::META_CALLBACKS] = $dp[\RectorPrefix20210514\Nette\Caching\Cache::CALLBACKS];
        }
        if (!isset($this->locks[$key])) {
            $this->lock($key);
            if (!isset($this->locks[$key])) {
                return;
            }
        }
        $handle = $this->locks[$key];
        unset($this->locks[$key]);
        $cacheFile = $this->getCacheFile($key);
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::TAGS]) || isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::PRIORITY])) {
            if (!$this->journal) {
                throw new \RectorPrefix20210514\Nette\InvalidStateException('CacheJournal has not been provided.');
            }
            $this->journal->write($cacheFile, $dp);
        }
        \ftruncate($handle, 0);
        if (!\is_string($data)) {
            $data = \serialize($data);
            $meta[self::META_SERIALIZED] = \true;
        }
        $head = \serialize($meta);
        $head = \str_pad((string) \strlen($head), 6, '0', \STR_PAD_LEFT) . $head;
        $headLen = \strlen($head);
        do {
            if (\fwrite($handle, \str_repeat("\0", $headLen)) !== $headLen) {
                break;
            }
            if (\fwrite($handle, $data) !== \strlen($data)) {
                break;
            }
            \fseek($handle, 0);
            if (\fwrite($handle, $head) !== $headLen) {
                break;
            }
            \flock($handle, \LOCK_UN);
            \fclose($handle);
            return;
        } while (\false);
        $this->delete($cacheFile, $handle);
    }
    public function remove(string $key) : void
    {
        unset($this->locks[$key]);
        $this->delete($this->getCacheFile($key));
    }
    public function clean(array $conditions) : void
    {
        $all = !empty($conditions[\RectorPrefix20210514\Nette\Caching\Cache::ALL]);
        $collector = empty($conditions);
        $namespaces = $conditions[\RectorPrefix20210514\Nette\Caching\Cache::NAMESPACES] ?? null;
        // cleaning using file iterator
        if ($all || $collector) {
            $now = \time();
            foreach (\RectorPrefix20210514\Nette\Utils\Finder::find('_*')->from($this->dir)->childFirst() as $entry) {
                $path = (string) $entry;
                if ($entry->isDir()) {
                    // collector: remove empty dirs
                    @\rmdir($path);
                    // @ - removing dirs is not necessary
                    continue;
                }
                if ($all) {
                    $this->delete($path);
                } else {
                    // collector
                    $meta = $this->readMetaAndLock($path, \LOCK_SH);
                    if (!$meta) {
                        continue;
                    }
                    if (!empty($meta[self::META_DELTA]) && \filemtime($meta[self::FILE]) + $meta[self::META_DELTA] < $now || !empty($meta[self::META_EXPIRE]) && $meta[self::META_EXPIRE] < $now) {
                        $this->delete($path, $meta[self::HANDLE]);
                        continue;
                    }
                    \flock($meta[self::HANDLE], \LOCK_UN);
                    \fclose($meta[self::HANDLE]);
                }
            }
            if ($this->journal) {
                $this->journal->clean($conditions);
            }
            return;
        } elseif ($namespaces) {
            foreach ($namespaces as $namespace) {
                $dir = $this->dir . '/_' . \urlencode($namespace);
                if (!\is_dir($dir)) {
                    continue;
                }
                foreach (\RectorPrefix20210514\Nette\Utils\Finder::findFiles('_*')->in($dir) as $entry) {
                    $this->delete((string) $entry);
                }
                @\rmdir($dir);
                // may already contain new files
            }
        }
        // cleaning using journal
        if ($this->journal) {
            foreach ($this->journal->clean($conditions) as $file) {
                $this->delete($file);
            }
        }
    }
    /**
     * Reads cache data from disk.
     */
    protected function readMetaAndLock(string $file, int $lock) : ?array
    {
        $handle = @\fopen($file, 'r+b');
        // @ - file may not exist
        if (!$handle) {
            return null;
        }
        \flock($handle, $lock);
        $size = (int) \stream_get_contents($handle, self::META_HEADER_LEN);
        if ($size) {
            $meta = \stream_get_contents($handle, $size, self::META_HEADER_LEN);
            $meta = \unserialize($meta);
            $meta[self::FILE] = $file;
            $meta[self::HANDLE] = $handle;
            return $meta;
        }
        \flock($handle, \LOCK_UN);
        \fclose($handle);
        return null;
    }
    /**
     * Reads cache data from disk and closes cache file handle.
     * @return mixed
     */
    protected function readData(array $meta)
    {
        $data = \stream_get_contents($meta[self::HANDLE]);
        \flock($meta[self::HANDLE], \LOCK_UN);
        \fclose($meta[self::HANDLE]);
        return empty($meta[self::META_SERIALIZED]) ? $data : \unserialize($data);
    }
    /**
     * Returns file name.
     */
    protected function getCacheFile(string $key) : string
    {
        $file = \urlencode($key);
        if ($a = \strrpos($file, '%00')) {
            // %00 = urlencode(Nette\Caching\Cache::NAMESPACE_SEPARATOR)
            $file = \substr_replace($file, '/_', $a, 3);
        }
        return $this->dir . '/_' . $file;
    }
    /**
     * Deletes and closes file.
     * @param  resource  $handle
     */
    private static function delete(string $file, $handle = null) : void
    {
        if (@\unlink($file)) {
            // @ - file may not already exist
            if ($handle) {
                \flock($handle, \LOCK_UN);
                \fclose($handle);
            }
            return;
        }
        if (!$handle) {
            $handle = @\fopen($file, 'r+');
            // @ - file may not exist
        }
        if (!$handle) {
            return;
        }
        \flock($handle, \LOCK_EX);
        \ftruncate($handle, 0);
        \flock($handle, \LOCK_UN);
        \fclose($handle);
        @\unlink($file);
        // @ - file may not already exist
    }
}
