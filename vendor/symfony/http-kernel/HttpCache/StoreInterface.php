<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This code is partially based on the Rack-Cache library by Ryan Tomayko,
 * which is released under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\HttpCache;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
/**
 * Interface implemented by HTTP cache stores.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface StoreInterface
{
    /**
     * Locates a cached Response for the Request provided.
     *
     * @return Response|null A Response instance, or null if no cache entry was found
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function lookup($request);
    /**
     * Writes a cache entry to the store for the given Request and Response.
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @return string The key under which the response is stored
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function write($request, $response);
    /**
     * Invalidates all cache entries that match the request.
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function invalidate($request);
    /**
     * Locks the cache for a given Request.
     *
     * @return bool|string true if the lock is acquired, the path to the current lock otherwise
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function lock($request);
    /**
     * Releases the lock for the given Request.
     *
     * @return bool False if the lock file does not exist or cannot be unlocked, true otherwise
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function unlock($request);
    /**
     * Returns whether or not a lock exists.
     *
     * @return bool true if lock exists, false otherwise
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function isLocked($request);
    /**
     * Purges data for the given URL.
     *
     * @return bool true if the URL exists and has been purged, false otherwise
     * @param string $url
     */
    public function purge($url);
    /**
     * Cleanups storage.
     */
    public function cleanup();
}
