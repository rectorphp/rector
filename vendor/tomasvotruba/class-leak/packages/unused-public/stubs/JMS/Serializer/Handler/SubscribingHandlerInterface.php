<?php

declare (strict_types=1);
namespace RectorPrefix202608\JMS\Serializer\Handler;

if (interface_exists(SubscribingHandlerInterface::class)) {
    return;
}
interface SubscribingHandlerInterface
{
    public static function getSubscribingMethods();
}
