<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject\ConstantMap;

final class SymfonyRequestConstantMap
{
    /**
     * @see https://github.com/symfony/symfony/blob/8e8207bb72d7f2cb8be355994ad2fcfa97c00f74/src/Symfony/Component/HttpFoundation/Request.php#L54-L63
     *
     * @var array<string, string>
     */
    public const METHOD_TO_CONST = ['GET' => 'METHOD_GET', 'POST' => 'METHOD_POST', 'HEAD' => 'METHOD_HEAD', 'PUT' => 'METHOD_PUT', 'PATCH' => 'METHOD_PATCH', 'DELETE' => 'METHOD_DELETE', 'PURGE' => 'METHOD_PURGE', 'OPTIONS' => 'METHOD_OPTIONS', 'TRACE' => 'METHOD_TRACE', 'CONNECT' => 'METHOD_CONNECT'];
}
