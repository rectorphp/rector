<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpFoundation;

/**
 * ResponseHeaderBag is a container for Response HTTP headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ResponseHeaderBag extends \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderBag
{
    public const COOKIES_FLAT = 'flat';
    public const COOKIES_ARRAY = 'array';
    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';
    protected $computedCacheControl = [];
    protected $cookies = [];
    protected $headerNames = [];
    public function __construct(array $headers = [])
    {
        parent::__construct($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('Cache-Control', '');
        }
        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!isset($this->headers['date'])) {
            $this->initDate();
        }
    }
    /**
     * Returns the headers, with original capitalizations.
     *
     * @return array An array of headers
     */
    public function allPreserveCase()
    {
        $headers = [];
        foreach ($this->all() as $name => $value) {
            $headers[$this->headerNames[$name] ?? $name] = $value;
        }
        return $headers;
    }
    public function allPreserveCaseWithoutCookies()
    {
        $headers = $this->allPreserveCase();
        if (isset($this->headerNames['set-cookie'])) {
            unset($headers[$this->headerNames['set-cookie']]);
        }
        return $headers;
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $headers
     */
    public function replace($headers = [])
    {
        $this->headerNames = [];
        parent::replace($headers);
        if (!isset($this->headers['cache-control'])) {
            $this->set('Cache-Control', '');
        }
        if (!isset($this->headers['date'])) {
            $this->initDate();
        }
    }
    /**
     * {@inheritdoc}
     * @param string|null $key
     */
    public function all($key = null)
    {
        $headers = parent::all();
        if (null !== $key) {
            $key = \strtr($key, self::UPPER, self::LOWER);
            return 'set-cookie' !== $key ? $headers[$key] ?? [] : \array_map('strval', $this->getCookies());
        }
        foreach ($this->getCookies() as $cookie) {
            $headers['set-cookie'][] = (string) $cookie;
        }
        return $headers;
    }
    /**
     * {@inheritdoc}
     * @param string $key
     * @param bool $replace
     */
    public function set($key, $values, $replace = \true)
    {
        $uniqueKey = \strtr($key, self::UPPER, self::LOWER);
        if ('set-cookie' === $uniqueKey) {
            if ($replace) {
                $this->cookies = [];
            }
            foreach ((array) $values as $cookie) {
                $this->setCookie(\RectorPrefix20211020\Symfony\Component\HttpFoundation\Cookie::fromString($cookie));
            }
            $this->headerNames[$uniqueKey] = $key;
            return;
        }
        $this->headerNames[$uniqueKey] = $key;
        parent::set($key, $values, $replace);
        // ensure the cache-control header has sensible defaults
        if (\in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], \true) && '' !== ($computed = $this->computeCacheControlValue())) {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }
    /**
     * {@inheritdoc}
     * @param string $key
     */
    public function remove($key)
    {
        $uniqueKey = \strtr($key, self::UPPER, self::LOWER);
        unset($this->headerNames[$uniqueKey]);
        if ('set-cookie' === $uniqueKey) {
            $this->cookies = [];
            return;
        }
        parent::remove($key);
        if ('cache-control' === $uniqueKey) {
            $this->computedCacheControl = [];
        }
        if ('date' === $uniqueKey) {
            $this->initDate();
        }
    }
    /**
     * {@inheritdoc}
     * @param string $key
     */
    public function hasCacheControlDirective($key)
    {
        return \array_key_exists($key, $this->computedCacheControl);
    }
    /**
     * {@inheritdoc}
     * @param string $key
     */
    public function getCacheControlDirective($key)
    {
        return $this->computedCacheControl[$key] ?? null;
    }
    /**
     * @param \Symfony\Component\HttpFoundation\Cookie $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        $this->headerNames['set-cookie'] = 'Set-Cookie';
    }
    /**
     * Removes a cookie from the array, but does not unset it in the browser.
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     */
    public function removeCookie($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }
        unset($this->cookies[$domain][$path][$name]);
        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);
            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }
        if (empty($this->cookies)) {
            unset($this->headerNames['set-cookie']);
        }
    }
    /**
     * Returns an array with all cookies.
     *
     * @return Cookie[]
     *
     * @throws \InvalidArgumentException When the $format is invalid
     * @param string $format
     */
    public function getCookies($format = self::COOKIES_FLAT)
    {
        if (!\in_array($format, [self::COOKIES_FLAT, self::COOKIES_ARRAY])) {
            throw new \InvalidArgumentException(\sprintf('Format "%s" invalid (%s).', $format, \implode(', ', [self::COOKIES_FLAT, self::COOKIES_ARRAY])));
        }
        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }
        $flattenedCookies = [];
        foreach ($this->cookies as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }
        return $flattenedCookies;
    }
    /**
     * Clears a cookie in the browser.
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string|null $sameSite
     */
    public function clearCookie($name, $path = '/', $domain = null, $secure = \false, $httpOnly = \true, $sameSite = null)
    {
        $this->setCookie(new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Cookie($name, null, 1, $path, $domain, $secure, $httpOnly, \false, $sameSite));
    }
    /**
     * @see HeaderUtils::makeDisposition()
     * @param string $disposition
     * @param string $filename
     * @param string $filenameFallback
     */
    public function makeDisposition($disposition, $filename, $filenameFallback = '')
    {
        return \RectorPrefix20211020\Symfony\Component\HttpFoundation\HeaderUtils::makeDisposition($disposition, $filename, $filenameFallback);
    }
    /**
     * Returns the calculated value of the cache-control header.
     *
     * This considers several other headers and calculates or modifies the
     * cache-control header to a sensible, conservative value.
     *
     * @return string
     */
    protected function computeCacheControlValue()
    {
        if (!$this->cacheControl) {
            if ($this->has('Last-Modified') || $this->has('Expires')) {
                return 'private, must-revalidate';
                // allows for heuristic expiration (RFC 7234 Section 4.2.2) in the case of "Last-Modified"
            }
            // conservative by default
            return 'no-cache, private';
        }
        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }
        // public if s-maxage is defined, private otherwise
        if (!isset($this->cacheControl['s-maxage'])) {
            return $header . ', private';
        }
        return $header;
    }
    private function initDate() : void
    {
        $this->set('Date', \gmdate('D, d M Y H:i:s') . ' GMT');
    }
}
