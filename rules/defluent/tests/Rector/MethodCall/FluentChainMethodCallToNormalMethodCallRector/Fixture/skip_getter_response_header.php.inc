<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\SomeResponse;

final class SkipGetterResponseHeader
{
    /**
     * @var SomeResponse
     */
    private $httpResponse;

    public function run($path)
    {
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Origin', '*');
    }

    /**
     * @return SomeResponse
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }
}
