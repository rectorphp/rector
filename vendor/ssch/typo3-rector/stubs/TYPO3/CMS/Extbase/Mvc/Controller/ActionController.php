<?php

namespace RectorPrefix20211020\TYPO3\CMS\Extbase\Mvc\Controller;

use RectorPrefix20211020\Psr\Http\Message\ResponseInterface;
use RectorPrefix20211020\TYPO3\CMS\Core\Http\ResponseFactoryInterface;
use RectorPrefix20211020\TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use RectorPrefix20211020\TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
if (\class_exists('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController')) {
    return;
}
class ActionController extends \RectorPrefix20211020\TYPO3\CMS\Extbase\Mvc\Controller\AbstractController
{
    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;
    /**
     * @var ViewInterface
     */
    protected $view;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @return void
     * @param string $actionName
     * @param string $controllerName
     * @param string $extensionName
     * @param mixed[]|null $arguments
     */
    public function forward($actionName, $controllerName = null, $extensionName = null, $arguments = null)
    {
    }
    /**
     * @return void
     * @param mixed[]|null $arguments
     */
    protected function redirect($actionName, $controllerName = null, $extensionName = null, $arguments = null, $pageUid = null, $delay = 0, $statusCode = 303)
    {
    }
    /**
     * @return void
     */
    protected function redirectToUri($uri, $delay = 0, $statusCode = 303)
    {
    }
    /**
     * @param string $html
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function htmlResponse($html = null)
    {
        $html = (string) $html;
        return $this->responseFactory->createHtmlResponse(isset($html) ? $html : $this->view->render());
    }
}
