<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyAttribute
{
    /**
     * @var string
     */
    public const AUTOWIRE = 'Symfony\\Component\\DependencyInjection\\Attribute\\Autowire';
    /**
     * @var string
     */
    public const EVENT_LISTENER_ATTRIBUTE = 'Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener';
    /**
     * @var string
     */
    public const ROUTE = 'Symfony\\Component\\Routing\\Attribute\\Route';
}
