<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source;

interface SomeResponse
{
    /**
     * Sends a HTTP header and replaces a previous one.
     * @param  string  header name
     * @param  string  header value
     * @return static
     */
    function setHeader($name, $value);
}
