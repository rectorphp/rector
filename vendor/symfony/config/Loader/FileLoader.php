<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\Config\Loader;

use RectorPrefix20220418\Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use RectorPrefix20220418\Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use RectorPrefix20220418\Symfony\Component\Config\Exception\LoaderLoadException;
use RectorPrefix20220418\Symfony\Component\Config\FileLocatorInterface;
use RectorPrefix20220418\Symfony\Component\Config\Resource\FileExistenceResource;
use RectorPrefix20220418\Symfony\Component\Config\Resource\GlobResource;
/**
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class FileLoader extends \RectorPrefix20220418\Symfony\Component\Config\Loader\Loader
{
    protected static $loading = [];
    protected $locator;
    /**
     * @var string|null
     */
    private $currentDir;
    public function __construct(\RectorPrefix20220418\Symfony\Component\Config\FileLocatorInterface $locator, string $env = null)
    {
        $this->locator = $locator;
        parent::__construct($env);
    }
    /**
     * Sets the current directory.
     */
    public function setCurrentDir(string $dir)
    {
        $this->currentDir = $dir;
    }
    /**
     * Returns the file locator used by this loader.
     */
    public function getLocator() : \RectorPrefix20220418\Symfony\Component\Config\FileLocatorInterface
    {
        return $this->locator;
    }
    /**
     * Imports a resource.
     *
     * @param mixed                $resource       A Resource
     * @param string|null          $type           The resource type or null if unknown
     * @param bool                 $ignoreErrors   Whether to ignore import errors or not
     * @param string|null          $sourceResource The original resource importing the new resource
     * @param mixed[]|string $exclude Glob patterns to exclude from the import
     *
     * @return mixed
     *
     * @throws LoaderLoadException
     * @throws FileLoaderImportCircularReferenceException
     * @throws FileLocatorFileNotFoundException
     */
    public function import($resource, string $type = null, bool $ignoreErrors = \false, string $sourceResource = null, $exclude = null)
    {
        if (\is_string($resource) && \strlen($resource) !== ($i = \strcspn($resource, '*?{[')) && \strpos($resource, "\n") === \false) {
            $excluded = [];
            foreach ((array) $exclude as $pattern) {
                foreach ($this->glob($pattern, \true, $_, \false, \true) as $path => $info) {
                    // normalize Windows slashes and remove trailing slashes
                    $excluded[\rtrim(\str_replace('\\', '/', $path), '/')] = \true;
                }
            }
            $ret = [];
            $isSubpath = 0 !== $i && \strpos(\substr($resource, 0, $i), '/') !== \false;
            foreach ($this->glob($resource, \false, $_, $ignoreErrors || !$isSubpath, \false, $excluded) as $path => $info) {
                if (null !== ($res = $this->doImport($path, 'glob' === $type ? null : $type, $ignoreErrors, $sourceResource))) {
                    $ret[] = $res;
                }
                $isSubpath = \true;
            }
            if ($isSubpath) {
                return isset($ret[1]) ? $ret : $ret[0] ?? null;
            }
        }
        return $this->doImport($resource, $type, $ignoreErrors, $sourceResource);
    }
    /**
     * @internal
     * @param mixed[]|\Symfony\Component\Config\Resource\GlobResource $resource
     */
    protected function glob(string $pattern, bool $recursive, &$resource = null, bool $ignoreErrors = \false, bool $forExclusion = \false, array $excluded = [])
    {
        if (\strlen($pattern) === ($i = \strcspn($pattern, '*?{['))) {
            $prefix = $pattern;
            $pattern = '';
        } elseif (0 === $i || \strpos(\substr($pattern, 0, $i), '/') === \false) {
            $prefix = '.';
            $pattern = '/' . $pattern;
        } else {
            $prefix = \dirname(\substr($pattern, 0, 1 + $i));
            $pattern = \substr($pattern, \strlen($prefix));
        }
        try {
            $prefix = $this->locator->locate($prefix, $this->currentDir, \true);
        } catch (\RectorPrefix20220418\Symfony\Component\Config\Exception\FileLocatorFileNotFoundException $e) {
            if (!$ignoreErrors) {
                throw $e;
            }
            $resource = [];
            foreach ($e->getPaths() as $path) {
                $resource[] = new \RectorPrefix20220418\Symfony\Component\Config\Resource\FileExistenceResource($path);
            }
            return;
        }
        $resource = new \RectorPrefix20220418\Symfony\Component\Config\Resource\GlobResource($prefix, $pattern, $recursive, $forExclusion, $excluded);
        yield from $resource;
    }
    /**
     * @param mixed $resource
     */
    private function doImport($resource, string $type = null, bool $ignoreErrors = \false, string $sourceResource = null)
    {
        try {
            $loader = $this->resolve($resource, $type);
            if ($loader instanceof self && null !== $this->currentDir) {
                $resource = $loader->getLocator()->locate($resource, $this->currentDir, \false);
            }
            $resources = \is_array($resource) ? $resource : [$resource];
            for ($i = 0; $i < ($resourcesCount = \count($resources)); ++$i) {
                if (isset(self::$loading[$resources[$i]])) {
                    if ($i == $resourcesCount - 1) {
                        throw new \RectorPrefix20220418\Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException(\array_keys(self::$loading));
                    }
                } else {
                    $resource = $resources[$i];
                    break;
                }
            }
            self::$loading[$resource] = \true;
            try {
                $ret = $loader->load($resource, $type);
            } finally {
                unset(self::$loading[$resource]);
            }
            return $ret;
        } catch (\RectorPrefix20220418\Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException $e) {
            throw $e;
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                // prevent embedded imports from nesting multiple exceptions
                if ($e instanceof \RectorPrefix20220418\Symfony\Component\Config\Exception\LoaderLoadException) {
                    throw $e;
                }
                throw new \RectorPrefix20220418\Symfony\Component\Config\Exception\LoaderLoadException($resource, $sourceResource, 0, $e, $type);
            }
        }
        return null;
    }
}
