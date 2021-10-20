<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Http;

use RectorPrefix20211020\Psr\Http\Message\ResponseInterface;
if (\class_exists('TYPO3\\CMS\\Extbase\\Http\\ForwardResponse')) {
    return;
}
class ForwardResponse implements \RectorPrefix20211020\Psr\Http\Message\ResponseInterface
{
    /**
     * @var string
     */
    private $actionName;
    /**
     * @var string|null
     */
    private $controllerName = null;
    /**
     * @var string|null
     */
    private $extensionName = null;
    /**
     * @var array
     */
    private $arguments = [];
    /**
     * @param string $actionName
     */
    public function __construct($actionName)
    {
        $actionName = (string) $actionName;
        $this->actionName = $actionName;
    }
    /**
     * @return $this
     * @param string $controllerName
     */
    public function withControllerName($controllerName)
    {
        $controllerName = (string) $controllerName;
        $clone = clone $this;
        $clone->controllerName = $controllerName;
        return $clone;
    }
    /**
     * @return $this
     */
    public function withoutControllerName()
    {
        $clone = clone $this;
        $clone->controllerName = null;
        return $clone;
    }
    /**
     * @return $this
     * @param string $extensionName
     */
    public function withExtensionName($extensionName)
    {
        $extensionName = (string) $extensionName;
        $clone = clone $this;
        $clone->extensionName = $extensionName;
        return $clone;
    }
    /**
     * @return $this
     */
    public function withoutExtensionName()
    {
        $clone = clone $this;
        $this->extensionName = null;
        return $clone;
    }
    /**
     * @return $this
     * @param mixed[] $arguments
     */
    public function withArguments($arguments)
    {
        $clone = clone $this;
        $clone->arguments = $arguments;
        return $clone;
    }
    /**
     * @return $this
     */
    public function withoutArguments()
    {
        $clone = clone $this;
        $this->arguments = [];
        return $clone;
    }
    /**
     * @param string $code
     * @param string $reasonPhrase
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function withStatus($code, $reasonPhrase = '')
    {
    }
}
