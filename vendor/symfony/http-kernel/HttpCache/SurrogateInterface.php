<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\HttpCache;

use RectorPrefix20211020\Symfony\Component\HttpFoundation\Request;
use RectorPrefix20211020\Symfony\Component\HttpFoundation\Response;
interface SurrogateInterface
{
    /**
     * Returns surrogate name.
     *
     * @return string
     */
    public function getName();
    /**
     * Returns a new cache strategy instance.
     *
     * @return ResponseCacheStrategyInterface A ResponseCacheStrategyInterface instance
     */
    public function createCacheStrategy();
    /**
     * Checks that at least one surrogate has Surrogate capability.
     *
     * @return bool true if one surrogate has Surrogate capability, false otherwise
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function hasSurrogateCapability($request);
    /**
     * Adds Surrogate-capability to the given Request.
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function addSurrogateCapability($request);
    /**
     * Adds HTTP headers to specify that the Response needs to be parsed for Surrogate.
     *
     * This method only adds an Surrogate HTTP header if the Response has some Surrogate tags.
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function addSurrogateControl($response);
    /**
     * Checks that the Response needs to be parsed for Surrogate tags.
     *
     * @return bool true if the Response needs to be parsed, false otherwise
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function needsParsing($response);
    /**
     * Renders a Surrogate tag.
     *
     * @param string $alt     An alternate URI
     * @param string $comment A comment to add as an esi:include tag
     *
     * @return string
     * @param string $uri
     * @param bool $ignoreErrors
     */
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = \true, $comment = '');
    /**
     * Replaces a Response Surrogate tags with the included resource content.
     *
     * @return Response
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function process($request, $response);
    /**
     * Handles a Surrogate from the cache.
     *
     * @param string $alt An alternative URI
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Exception
     * @param \Symfony\Component\HttpKernel\HttpCache\HttpCache $cache
     * @param string $uri
     * @param bool $ignoreErrors
     */
    public function handle($cache, $uri, $alt, $ignoreErrors);
}
