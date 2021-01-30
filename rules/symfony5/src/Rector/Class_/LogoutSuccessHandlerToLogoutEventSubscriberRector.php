<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony5\NodeFactory\OnSuccessLogoutClassMethodFactory;
use Rector\SymfonyCodeQuality\NodeFactory\GetSubscribedEventsClassMethodFactory;
use Rector\SymfonyCodeQuality\ValueObject\EventReferenceToMethodNameWithPriority;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/pull/36243
 *
 * @see \Rector\Symfony5\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector\LogoutSuccessHandlerToLogoutEventSubscriberRectorTest
 */
final class LogoutSuccessHandlerToLogoutEventSubscriberRector extends AbstractRector
{
    /**
     * @var string
     */
    private const LOGOUT_SUCCESS_HANDLER_TYPE = 'Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface';

    /**
     * @var GetSubscribedEventsClassMethodFactory
     */
    private $getSubscribedEventsClassMethodFactory;

    /**
     * @var OnSuccessLogoutClassMethodFactory
     */
    private $onSuccessLogoutClassMethodFactory;

    public function __construct(
        OnSuccessLogoutClassMethodFactory $onSuccessLogoutClassMethodFactory,
        GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory
    ) {
        $this->getSubscribedEventsClassMethodFactory = $getSubscribedEventsClassMethodFactory;
        $this->onSuccessLogoutClassMethodFactory = $onSuccessLogoutClassMethodFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change logout success handler to an event listener that listens to LogoutEvent', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class SomeLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
      * @var HttpUtils
      */
    private $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    public function onLogoutSuccess(Request $request)
    {
        $response = $this->httpUtils->createRedirectResponse($request, 'some_url');
        return $response;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class SomeLogoutHandler implements EventSubscriberInterface
{
    /**
      * @var HttpUtils
      */
    private $httpUtils;

    public function onLogout(LogoutEvent $logoutEvent): void
    {
        if ($logoutEvent->getResponse() !== null) {
            return;
        }

        $response = $this->httpUtils->createRedirectResponse($logoutEvent->getRequest(), 'some_url');
        $logoutEvent->setResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => [['onLogout', 64]],
        ];
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, self::LOGOUT_SUCCESS_HANDLER_TYPE)) {
            return null;
        }

        $this->refactorImplements($node);

        // 2. refactor logout() class method to onLogout()
        $onLogoutSuccessClassMethod = $node->getMethod('onLogoutSuccess');
        if (! $onLogoutSuccessClassMethod instanceof ClassMethod) {
            return null;
        }

        $node->stmts[] = $this->onSuccessLogoutClassMethodFactory->createFromOnLogoutSuccessClassMethod(
            $onLogoutSuccessClassMethod
        );

        // 3. add getSubscribedEvents() class method
        $classConstFetch = $this->nodeFactory->createClassConstReference(
            'Symfony\Component\Security\Http\Event\LogoutEvent'
        );

        $eventReferencesToMethodNames = [new EventReferenceToMethodNameWithPriority($classConstFetch, 'onLogout', 64)];
        $getSubscribedEventsClassMethod = $this->getSubscribedEventsClassMethodFactory->create(
            $eventReferencesToMethodNames
        );
        $node->stmts[] = $getSubscribedEventsClassMethod;

        $this->removeNode($onLogoutSuccessClassMethod);

        return $node;
    }

    private function refactorImplements(Class_ $class): void
    {
        $class->implements[] = new FullyQualified('Symfony\Component\EventDispatcher\EventSubscriberInterface');

        foreach ($class->implements as $key => $implement) {
            if (! $this->isName($implement, self::LOGOUT_SUCCESS_HANDLER_TYPE)) {
                continue;
            }

            unset($class->implements[$key]);
        }
    }
}
