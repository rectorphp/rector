<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\Finder\Iterator;

use RectorPrefix20220501\Symfony\Component\Finder\Exception\AccessDeniedException;
use RectorPrefix20220501\Symfony\Component\Finder\SplFileInfo;
/**
 * Extends the \RecursiveDirectoryIterator to support relative paths.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    /**
     * @var bool
     */
    private $ignoreUnreadableDirs;
    /**
     * @var bool|null
     */
    private $rewindable;
    // these 3 properties take part of the performance optimization to avoid redoing the same work in all iterations
    /**
     * @var string
     */
    private $rootPath;
    /**
     * @var string
     */
    private $subPath;
    /**
     * @var string
     */
    private $directorySeparator = '/';
    /**
     * @throws \RuntimeException
     */
    public function __construct(string $path, int $flags, bool $ignoreUnreadableDirs = \false)
    {
        if ($flags & (self::CURRENT_AS_PATHNAME | self::CURRENT_AS_SELF)) {
            throw new \RuntimeException('This iterator only support returning current as fileinfo.');
        }
        parent::__construct($path, $flags);
        $this->ignoreUnreadableDirs = $ignoreUnreadableDirs;
        $this->rootPath = $path;
        if ('/' !== \DIRECTORY_SEPARATOR && !($flags & self::UNIX_PATHS)) {
            $this->directorySeparator = \DIRECTORY_SEPARATOR;
        }
    }
    /**
     * Return an instance of SplFileInfo with support for relative paths.
     */
    public function current() : \RectorPrefix20220501\Symfony\Component\Finder\SplFileInfo
    {
        // the logic here avoids redoing the same work in all iterations
        if (!isset($this->subPath)) {
            $this->subPath = $this->getSubPath();
        }
        $subPathname = $this->subPath;
        if ('' !== $subPathname) {
            $subPathname .= $this->directorySeparator;
        }
        $subPathname .= $this->getFilename();
        if ('/' !== ($basePath = $this->rootPath)) {
            $basePath .= $this->directorySeparator;
        }
        return new \RectorPrefix20220501\Symfony\Component\Finder\SplFileInfo($basePath . $subPathname, $this->subPath, $subPathname);
    }
    public function hasChildren($allowLinks = \false) : bool
    {
        $hasChildren = parent::hasChildren($allowLinks);
        if (!$hasChildren || !$this->ignoreUnreadableDirs) {
            return $hasChildren;
        }
        try {
            parent::getChildren();
            return \true;
        } catch (\UnexpectedValueException $e) {
            // If directory is unreadable and finder is set to ignore it, skip children
            return \false;
        }
    }
    /**
     * @throws AccessDeniedException
     */
    public function getChildren() : \RecursiveDirectoryIterator
    {
        try {
            $children = parent::getChildren();
            if ($children instanceof self) {
                // parent method will call the constructor with default arguments, so unreadable dirs won't be ignored anymore
                $children->ignoreUnreadableDirs = $this->ignoreUnreadableDirs;
                // performance optimization to avoid redoing the same work in all children
                $children->rewindable =& $this->rewindable;
                $children->rootPath = $this->rootPath;
            }
            return $children;
        } catch (\UnexpectedValueException $e) {
            throw new \RectorPrefix20220501\Symfony\Component\Finder\Exception\AccessDeniedException($e->getMessage(), $e->getCode(), $e);
        }
    }
    /**
     * Do nothing for non rewindable stream.
     */
    public function rewind() : void
    {
        if (\false === $this->isRewindable()) {
            return;
        }
        parent::rewind();
    }
    /**
     * Checks if the stream is rewindable.
     */
    public function isRewindable() : bool
    {
        if (null !== $this->rewindable) {
            return $this->rewindable;
        }
        if (\false !== ($stream = @\opendir($this->getPath()))) {
            $infos = \stream_get_meta_data($stream);
            \closedir($stream);
            if ($infos['seekable']) {
                return $this->rewindable = \true;
            }
        }
        return $this->rewindable = \false;
    }
}
