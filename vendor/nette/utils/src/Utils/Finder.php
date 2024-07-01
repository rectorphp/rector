<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202407\Nette\Utils;

use RectorPrefix202407\Nette;
/**
 * Finder allows searching through directory trees using iterator.
 *
 * Finder::findFiles('*.php')
 *     ->size('> 10kB')
 *     ->from('.')
 *     ->exclude('temp');
 *
 * @implements \IteratorAggregate<string, FileInfo>
 */
class Finder implements \IteratorAggregate
{
    use Nette\SmartObject;
    /** @var array<array{string, string}> */
    private $find = [];
    /** @var string[] */
    private $in = [];
    /** @var \Closure[] */
    private $filters = [];
    /** @var \Closure[] */
    private $descentFilters = [];
    /** @var array<string|self> */
    private $appends = [];
    /**
     * @var bool
     */
    private $childFirst = \false;
    /** @var ?callable */
    private $sort;
    /**
     * @var int
     */
    private $maxDepth = -1;
    /**
     * @var bool
     */
    private $ignoreUnreadableDirs = \true;
    /**
     * Begins search for files and directories matching mask.
     * @param string|mixed[] $masks
     * @return static
     */
    public static function find($masks = ['*'])
    {
        $masks = \is_array($masks) ? $masks : \func_get_args();
        // compatibility with variadic
        return (new static())->addMask($masks, 'dir')->addMask($masks, 'file');
    }
    /**
     * Begins search for files matching mask.
     * @param string|mixed[] $masks
     * @return static
     */
    public static function findFiles($masks = ['*'])
    {
        $masks = \is_array($masks) ? $masks : \func_get_args();
        // compatibility with variadic
        return (new static())->addMask($masks, 'file');
    }
    /**
     * Begins search for directories matching mask.
     * @param string|mixed[] $masks
     * @return static
     */
    public static function findDirectories($masks = ['*'])
    {
        $masks = \is_array($masks) ? $masks : \func_get_args();
        // compatibility with variadic
        return (new static())->addMask($masks, 'dir');
    }
    /**
     * Finds files matching the specified masks.
     * @param string|mixed[] $masks
     * @return static
     */
    public function files($masks = ['*'])
    {
        return $this->addMask((array) $masks, 'file');
    }
    /**
     * Finds directories matching the specified masks.
     * @param string|mixed[] $masks
     * @return static
     */
    public function directories($masks = ['*'])
    {
        return $this->addMask((array) $masks, 'dir');
    }
    /**
     * @return static
     */
    private function addMask(array $masks, string $mode)
    {
        foreach ($masks as $mask) {
            $mask = FileSystem::unixSlashes($mask);
            if ($mode === 'dir') {
                $mask = \rtrim($mask, '/');
            }
            if ($mask === '' || $mode === 'file' && \substr_compare($mask, '/', -\strlen('/')) === 0) {
                throw new Nette\InvalidArgumentException("Invalid mask '{$mask}'");
            }
            if (\strncmp($mask, '**/', \strlen('**/')) === 0) {
                $mask = \substr($mask, 3);
            }
            $this->find[] = [$mask, $mode];
        }
        return $this;
    }
    /**
     * Searches in the given directories. Wildcards are allowed.
     * @param string|mixed[] $paths
     * @return static
     */
    public function in($paths)
    {
        $paths = \is_array($paths) ? $paths : \func_get_args();
        // compatibility with variadic
        $this->addLocation($paths, '');
        return $this;
    }
    /**
     * Searches recursively from the given directories. Wildcards are allowed.
     * @param string|mixed[] $paths
     * @return static
     */
    public function from($paths)
    {
        $paths = \is_array($paths) ? $paths : \func_get_args();
        // compatibility with variadic
        $this->addLocation($paths, '/**');
        return $this;
    }
    private function addLocation(array $paths, string $ext) : void
    {
        foreach ($paths as $path) {
            if ($path === '') {
                throw new Nette\InvalidArgumentException("Invalid directory '{$path}'");
            }
            $path = \rtrim(FileSystem::unixSlashes($path), '/');
            $this->in[] = $path . $ext;
        }
    }
    /**
     * Lists directory's contents before the directory itself. By default, this is disabled.
     * @return static
     */
    public function childFirst(bool $state = \true)
    {
        $this->childFirst = $state;
        return $this;
    }
    /**
     * Ignores unreadable directories. By default, this is enabled.
     * @return static
     */
    public function ignoreUnreadableDirs(bool $state = \true)
    {
        $this->ignoreUnreadableDirs = $state;
        return $this;
    }
    /**
     * Set a compare function for sorting directory entries. The function will be called to sort entries from the same directory.
     * @param  callable(FileInfo, FileInfo): int  $callback
     * @return static
     */
    public function sortBy(callable $callback)
    {
        $this->sort = $callback;
        return $this;
    }
    /**
     * Sorts files in each directory naturally by name.
     * @return static
     */
    public function sortByName()
    {
        $this->sort = function (FileInfo $a, FileInfo $b) : int {
            return \strnatcmp($a->getBasename(), $b->getBasename());
        };
        return $this;
    }
    /**
     * Adds the specified paths or appends a new finder that returns.
     * @param string|mixed[]|null $paths
     * @return static
     */
    public function append($paths = null)
    {
        if ($paths === null) {
            return $this->appends[] = new static();
        }
        $this->appends = \array_merge($this->appends, (array) $paths);
        return $this;
    }
    /********************* filtering ****************d*g**/
    /**
     * Skips entries that matches the given masks relative to the ones defined with the in() or from() methods.
     * @param string|mixed[] $masks
     * @return static
     */
    public function exclude($masks)
    {
        $masks = \is_array($masks) ? $masks : \func_get_args();
        // compatibility with variadic
        foreach ($masks as $mask) {
            $mask = FileSystem::unixSlashes($mask);
            if (!\preg_match('~^/?(\\*\\*/)?(.+)(/\\*\\*|/\\*|/|)$~D', $mask, $m)) {
                throw new Nette\InvalidArgumentException("Invalid mask '{$mask}'");
            }
            $end = $m[3];
            $re = $this->buildPattern($m[2]);
            $filter = function (FileInfo $file) use($end, $re) : bool {
                return $end && !$file->isDir() || !\preg_match($re, FileSystem::unixSlashes($file->getRelativePathname()));
            };
            $this->descentFilter($filter);
            if ($end !== '/*') {
                $this->filter($filter);
            }
        }
        return $this;
    }
    /**
     * Yields only entries which satisfy the given filter.
     * @param  callable(FileInfo): bool  $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        $this->filters[] = \Closure::fromCallable($callback);
        return $this;
    }
    /**
     * It descends only to directories that match the specified filter.
     * @param  callable(FileInfo): bool  $callback
     * @return static
     */
    public function descentFilter(callable $callback)
    {
        $this->descentFilters[] = \Closure::fromCallable($callback);
        return $this;
    }
    /**
     * Sets the maximum depth of entries.
     * @return static
     */
    public function limitDepth(?int $depth)
    {
        $this->maxDepth = $depth ?? -1;
        return $this;
    }
    /**
     * Restricts the search by size. $operator accepts "[operator] [size] [unit]" example: >=10kB
     * @return static
     */
    public function size(string $operator, ?int $size = null)
    {
        if (\func_num_args() === 1) {
            // in $operator is predicate
            if (!\preg_match('#^(?:([=<>!]=?|<>)\\s*)?((?:\\d*\\.)?\\d+)\\s*(K|M|G|)B?$#Di', $operator, $matches)) {
                throw new Nette\InvalidArgumentException('Invalid size predicate format.');
            }
            [, $operator, $size, $unit] = $matches;
            $units = ['' => 1, 'k' => 1000.0, 'm' => 1000000.0, 'g' => 1000000000.0];
            $size *= $units[\strtolower($unit)];
            $operator = $operator ?: '=';
        }
        return $this->filter(function (FileInfo $file) use($operator, $size) : bool {
            return !$file->isFile() || Helpers::compare($file->getSize(), $operator, $size);
        });
    }
    /**
     * Restricts the search by modified time. $operator accepts "[operator] [date]" example: >1978-01-23
     * @param string|int|\DateTimeInterface|null $date
     * @return static
     */
    public function date(string $operator, $date = null)
    {
        if (\func_num_args() === 1) {
            // in $operator is predicate
            if (!\preg_match('#^(?:([=<>!]=?|<>)\\s*)?(.+)$#Di', $operator, $matches)) {
                throw new Nette\InvalidArgumentException('Invalid date predicate format.');
            }
            [, $operator, $date] = $matches;
            $operator = $operator ?: '=';
        }
        $date = DateTime::from($date)->format('U');
        return $this->filter(function (FileInfo $file) use($operator, $date) : bool {
            return !$file->isFile() || Helpers::compare($file->getMTime(), $operator, $date);
        });
    }
    /********************* iterator generator ****************d*g**/
    /**
     * Returns an array with all found files and directories.
     * @return list<FileInfo>
     */
    public function collect() : array
    {
        return \iterator_to_array($this->getIterator(), \false);
    }
    /** @return \Generator<string, FileInfo> */
    public function getIterator() : \Generator
    {
        $plan = $this->buildPlan();
        foreach ($plan as $dir => $searches) {
            yield from $this->traverseDir($dir, $searches);
        }
        foreach ($this->appends as $item) {
            if ($item instanceof self) {
                yield from $item->getIterator();
            } else {
                $item = FileSystem::platformSlashes($item);
                (yield $item => new FileInfo($item));
            }
        }
    }
    /**
     * @param  array<object{pattern: string, mode: string, recursive: bool}>  $searches
     * @param  string[]  $subdirs
     * @return \Generator<string, FileInfo>
     */
    private function traverseDir(string $dir, array $searches, array $subdirs = []) : \Generator
    {
        if ($this->maxDepth >= 0 && \count($subdirs) > $this->maxDepth) {
            return;
        } elseif (!\is_dir($dir)) {
            throw new Nette\InvalidStateException(\sprintf("Directory '%s' does not exist.", \rtrim($dir, '/\\')));
        }
        try {
            $pathNames = new \FilesystemIterator($dir, \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::UNIX_PATHS);
        } catch (\UnexpectedValueException $e) {
            if ($this->ignoreUnreadableDirs) {
                return;
            } else {
                throw new Nette\InvalidStateException($e->getMessage());
            }
        }
        $files = $this->convertToFiles($pathNames, \implode('/', $subdirs), FileSystem::isAbsolute($dir));
        if ($this->sort) {
            $files = \iterator_to_array($files);
            \usort($files, $this->sort);
        }
        foreach ($files as $file) {
            $pathName = $file->getPathname();
            $cache = $subSearch = [];
            if ($file->isDir()) {
                foreach ($searches as $search) {
                    if ($search->recursive && $this->proveFilters($this->descentFilters, $file, $cache)) {
                        $subSearch[] = $search;
                    }
                }
            }
            if ($this->childFirst && $subSearch) {
                yield from $this->traverseDir($pathName, $subSearch, \array_merge($subdirs, [$file->getBasename()]));
            }
            $relativePathname = FileSystem::unixSlashes($file->getRelativePathname());
            foreach ($searches as $search) {
                if ($file->{'is' . $search->mode}() && \preg_match($search->pattern, $relativePathname) && $this->proveFilters($this->filters, $file, $cache)) {
                    (yield $pathName => $file);
                    break;
                }
            }
            if (!$this->childFirst && $subSearch) {
                yield from $this->traverseDir($pathName, $subSearch, \array_merge($subdirs, [$file->getBasename()]));
            }
        }
    }
    private function convertToFiles(iterable $pathNames, string $relativePath, bool $absolute) : \Generator
    {
        foreach ($pathNames as $pathName) {
            if (!$absolute) {
                $pathName = \preg_replace('~\\.?/~A', '', $pathName);
            }
            $pathName = FileSystem::platformSlashes($pathName);
            (yield new FileInfo($pathName, $relativePath));
        }
    }
    private function proveFilters(array $filters, FileInfo $file, array &$cache) : bool
    {
        foreach ($filters as $filter) {
            $res =& $cache[\spl_object_id($filter)];
            $res = $res ?? $filter($file);
            if (!$res) {
                return \false;
            }
        }
        return \true;
    }
    /** @return array<string, array<object{pattern: string, mode: string, recursive: bool}>> */
    private function buildPlan() : array
    {
        $plan = $dirCache = [];
        foreach ($this->find as [$mask, $mode]) {
            $splits = [];
            if (FileSystem::isAbsolute($mask)) {
                if ($this->in) {
                    throw new Nette\InvalidStateException("You cannot combine the absolute path in the mask '{$mask}' and the directory to search '{$this->in[0]}'.");
                }
                $splits[] = self::splitRecursivePart($mask);
            } else {
                foreach ($this->in ?: ['.'] as $in) {
                    $in = \strtr($in, ['[' => '[[]', ']' => '[]]']);
                    // in path, do not treat [ and ] as a pattern by glob()
                    $splits[] = self::splitRecursivePart($in . '/' . $mask);
                }
            }
            foreach ($splits as [$base, $rest, $recursive]) {
                $base = $base === '' ? '.' : $base;
                $dirs = $dirCache[$base] = $dirCache[$base] ?? (\strpbrk($base, '*?[') ? \glob($base, \GLOB_NOSORT | \GLOB_ONLYDIR | \GLOB_NOESCAPE) : [\strtr($base, ['[[]' => '[', '[]]' => ']'])]);
                // unescape [ and ]
                if (!$dirs) {
                    throw new Nette\InvalidStateException(\sprintf("Directory '%s' does not exist.", \rtrim($base, '/\\')));
                }
                $search = (object) ['pattern' => $this->buildPattern($rest), 'mode' => $mode, 'recursive' => $recursive];
                foreach ($dirs as $dir) {
                    $plan[$dir][] = $search;
                }
            }
        }
        return $plan;
    }
    /**
     * Since glob() does not know ** wildcard, we divide the path into a part for glob and a part for manual traversal.
     */
    private static function splitRecursivePart(string $path) : array
    {
        $a = \strrpos($path, '/');
        $parts = \preg_split('~(?<=^|/)\\*\\*($|/)~', \substr($path, 0, $a + 1), 2);
        return isset($parts[1]) ? [$parts[0], $parts[1] . \substr($path, $a + 1), \true] : [$parts[0], \substr($path, $a + 1), \false];
    }
    /**
     * Converts wildcards to regular expression.
     */
    private function buildPattern(string $mask) : string
    {
        if ($mask === '*') {
            return '##';
        } elseif (\strncmp($mask, './', \strlen('./')) === 0) {
            $anchor = '^';
            $mask = \substr($mask, 2);
        } else {
            $anchor = '(?:^|/)';
        }
        $pattern = \strtr(\preg_quote($mask, '#'), ['\\*\\*/' => '(.+/)?', '\\*' => '[^/]*', '\\?' => '[^/]', '\\[\\!' => '[^', '\\[' => '[', '\\]' => ']', '\\-' => '-']);
        return '#' . $anchor . $pattern . '$#D' . (\defined('PHP_WINDOWS_VERSION_BUILD') ? 'i' : '');
    }
}
