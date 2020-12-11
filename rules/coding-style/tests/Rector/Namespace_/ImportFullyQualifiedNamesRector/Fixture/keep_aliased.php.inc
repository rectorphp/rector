<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Symfony\Component\HttpFoundation\Request as HttpRequest;

final class KeepAliased
{
    private const SUPPORTED_HTTP_METHODS = [
        HttpRequest::METHOD_GET
    ];
}
