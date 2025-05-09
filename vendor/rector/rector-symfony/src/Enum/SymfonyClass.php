<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyClass
{
    /**
     * @var string
     */
    public const CONTROLLER = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller';
    /**
     * @var string
     */
    public const RESPONSE = 'Symfony\\Component\\HttpFoundation\\Response';
    /**
     * @var string
     */
    public const COMMAND = 'Symfony\\Component\\Console\\Command\\Command';
    /**
     * @var string
     */
    public const CONTAINER_AWARE_COMMAND = 'Symfony\\Bundle\\FrameworkBundle\\Command\\ContainerAwareCommand';
    /**
     * @var string
     */
    public const EVENT_DISPATCHER_INTERFACE = 'Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface';
    /**
     * @var string
     */
    public const VALIDATOR_INTERFACE = 'Symfony\\Component\\Validator\\Validator\\ValidatorInterface';
    /**
     * @var string
     */
    public const LOGGER_INTERFACE = 'Psr\\Log\\LoggerInterface';
    /**
     * @var string
     */
    public const JMS_SERIALIZER_INTERFACE = 'JMS\\Serializer\\SerializerInterface';
    /**
     * @var string
     */
    public const KERNEL_EVENTS_CLASS = 'Symfony\\Component\\HttpKernel\\KernelEvents';
    /**
     * @var string
     */
    public const CONSOLE_EVENTS_CLASS = 'Symfony\\Component\\Console\\ConsoleEvents';
    /**
     * @var string
     */
    public const EVENT_SUBSCRIBER_INTERFACE = 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface';
    /**
     * @var string
     */
    public const TRANSLATOR_INTERFACE = 'Symfony\\Contracts\\Translation\\TranslatorInterface';
    /**
     * @var string
     */
    public const SESSION_INTERFACRE = 'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface';
    /**
     * @var string
     */
    public const TOKEN_STORAGE_INTERFACE = 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\Storage\\TokenStorageInterface';
    /**
     * @var string
     */
    public const HTTP_KERNEL_INTERFACE = 'Symfony\\Component\\HttpKernel\\HttpKernelInterface';
    /**
     * @var string
     */
    public const HTTP_KERNEL = 'Symfony\\Component\\HttpKernel\\HttpKernel';
    /**
     * @var string
     */
    public const REQUEST = 'Symfony\\Component\\HttpFoundation\\Request';
    /**
     * @var string
     */
    public const ABSTRACT_CONTROLLER = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController';
    /**
     * @var string
     */
    public const CONTROLLER_TRAIT = 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerTrait';
    /**
     * @var string
     */
    public const AUTHORIZATION_CHECKER = 'Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface';
    public const REQUEST_STACK = 'Symfony\\Component\\HttpFoundation\\RequestStack';
}
