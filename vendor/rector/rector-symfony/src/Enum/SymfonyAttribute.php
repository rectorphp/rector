<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyAttribute
{
    /**
     * @var string
     */
    public const AUTOWIRE = 'Symfony\Component\DependencyInjection\Attribute\Autowire';
    /**
     * @var string
     */
    public const AS_COMMAND = 'Symfony\Component\Console\Attribute\AsCommand';
    /**
     * @var string
     */
    public const COMMAND_OPTION = 'Symfony\Component\Console\Attribute\Option';
    /**
     * @var string
     */
    public const COMMAND_ARGUMENT = 'Symfony\Component\Console\Attribute\Argument';
    /**
     * @var string
     */
    public const AS_EVENT_LISTENER = 'Symfony\Component\EventDispatcher\Attribute\AsEventListener';
    // Workflow listener attributes (Symfony 7.1+)
    /**
     * @var string
     */
    public const AS_ANNOUNCE_LISTENER = 'Symfony\Component\Workflow\Attribute\AsAnnounceListener';
    /**
     * @var string
     */
    public const AS_COMPLETED_LISTENER = 'Symfony\Component\Workflow\Attribute\AsCompletedListener';
    /**
     * @var string
     */
    public const AS_ENTER_LISTENER = 'Symfony\Component\Workflow\Attribute\AsEnterListener';
    /**
     * @var string
     */
    public const AS_ENTERED_LISTENER = 'Symfony\Component\Workflow\Attribute\AsEnteredListener';
    /**
     * @var string
     */
    public const AS_GUARD_LISTENER = 'Symfony\Component\Workflow\Attribute\AsGuardListener';
    /**
     * @var string
     */
    public const AS_LEAVE_LISTENER = 'Symfony\Component\Workflow\Attribute\AsLeaveListener';
    /**
     * @var string
     */
    public const AS_TRANSITION_LISTENER = 'Symfony\Component\Workflow\Attribute\AsTransitionListener';
    /**
     * @var string
     */
    public const ROUTE = 'Symfony\Component\Routing\Attribute\Route';
    /**
     * @var string
     */
    public const IS_GRANTED = 'Symfony\Component\Security\Http\Attribute\IsGranted';
    /**
     * @var string
     */
    public const REQUIRED = 'Symfony\Contracts\Service\Attribute\Required';
}
