<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\Profiler;

use RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
/**
 * Profile.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Profile
{
    private $token;
    /**
     * @var DataCollectorInterface[]
     */
    private $collectors = [];
    private $ip;
    private $method;
    private $url;
    private $time;
    private $statusCode;
    /**
     * @var Profile
     */
    private $parent;
    /**
     * @var Profile[]
     */
    private $children = [];
    public function __construct(string $token)
    {
        $this->token = $token;
    }
    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    /**
     * Gets the token.
     *
     * @return string The token
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * Sets the parent token.
     * @param $this $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    /**
     * Returns the parent profile.
     *
     * @return self
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * Returns the parent token.
     *
     * @return string|null The parent token
     */
    public function getParentToken()
    {
        return $this->parent ? $this->parent->getToken() : null;
    }
    /**
     * Returns the IP.
     *
     * @return string|null The IP
     */
    public function getIp()
    {
        return $this->ip;
    }
    /**
     * @param string|null $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
    /**
     * Returns the request method.
     *
     * @return string|null The request method
     */
    public function getMethod()
    {
        return $this->method;
    }
    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }
    /**
     * Returns the URL.
     *
     * @return string|null The URL
     */
    public function getUrl()
    {
        return $this->url;
    }
    /**
     * @param string|null $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    /**
     * @return int The time
     */
    public function getTime()
    {
        return $this->time ?? 0;
    }
    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
    /**
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    /**
     * Finds children profilers.
     *
     * @return self[]
     */
    public function getChildren()
    {
        return $this->children;
    }
    /**
     * Sets children profiler.
     *
     * @param Profile[] $children
     */
    public function setChildren($children)
    {
        $this->children = [];
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }
    /**
     * Adds the child token.
     * @param $this $child
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }
    /**
     * @param string $token
     */
    public function getChildByToken($token) : ?self
    {
        foreach ($this->children as $child) {
            if ($token === $child->getToken()) {
                return $child;
            }
        }
        return null;
    }
    /**
     * Gets a Collector by name.
     *
     * @return DataCollectorInterface A DataCollectorInterface instance
     *
     * @throws \InvalidArgumentException if the collector does not exist
     * @param string $name
     */
    public function getCollector($name)
    {
        if (!isset($this->collectors[$name])) {
            throw new \InvalidArgumentException(\sprintf('Collector "%s" does not exist.', $name));
        }
        return $this->collectors[$name];
    }
    /**
     * Gets the Collectors associated with this profile.
     *
     * @return DataCollectorInterface[]
     */
    public function getCollectors()
    {
        return $this->collectors;
    }
    /**
     * Sets the Collectors associated with this profile.
     *
     * @param DataCollectorInterface[] $collectors
     */
    public function setCollectors($collectors)
    {
        $this->collectors = [];
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }
    }
    /**
     * Adds a Collector.
     * @param \Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface $collector
     */
    public function addCollector($collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }
    /**
     * @return bool
     * @param string $name
     */
    public function hasCollector($name)
    {
        return isset($this->collectors[$name]);
    }
    /**
     * @return array
     */
    public function __sleep()
    {
        return ['token', 'parent', 'children', 'collectors', 'ip', 'method', 'url', 'time', 'statusCode'];
    }
}
