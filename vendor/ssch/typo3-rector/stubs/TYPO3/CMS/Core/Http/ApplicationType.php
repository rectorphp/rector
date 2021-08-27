<?php

namespace RectorPrefix20210827\TYPO3\CMS\Core\Http;

use RectorPrefix20210827\Psr\Http\Message\ServerRequestInterface;
use RectorPrefix20210827\TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
if (\class_exists('TYPO3\\CMS\\Core\\Http\\ApplicationType')) {
    return;
}
class ApplicationType
{
    /**
     * @param string $type
     */
    private function __construct($type)
    {
    }
    /**
     * @return $this
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public static function fromRequest($request)
    {
        return new self('foo');
    }
    /**
     * @return bool
     */
    public function isFrontend()
    {
        return \true;
    }
    /**
     * @return bool
     */
    public function isBackend()
    {
        return \true;
    }
}
