<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory;
use Rector\Symfony\NodeFactory\OnSuccessLogoutClassMethodFactory;
use Rector\Symfony\ValueObject\EventReferenceToMethodNameWithPriority;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/36243
 *
 * @see \Rector\Symfony\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector\LogoutSuccessHandlerToLogoutEventSubscriberRectorTest
 */
final class LogoutSuccessHandlerToLogoutEventSubscriberRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $successHandlerObjectType;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\OnSuccessLogoutClassMethodFactory
     */
    private $onSuccessLogoutClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory
     */
    private $getSubscribedEventsClassMethodFactory;
    public function __construct(\Rector\Symfony\NodeFactory\OnSuccessLogoutClassMethodFactory $onSuccessLogoutClassMethodFactory, \Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory)
    {
        $this->onSuccessLogoutClassMethodFactory = $onSuccessLogoutClassMethodFactory;
        $this->getSubscribedEventsClassMethodFactory = $getSubscribedEventsClassMethodFactory;
        $this->successHandlerObjectType = new \PHPStan\Type\ObjectType('Symfony\\Component\\Security\\Http\\Logout\\LogoutSuccessHandlerInterface');
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change logout success handler to an event listener that listens to LogoutEvent', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, $this->successHandlerObjectType)) {
            return null;
        }
        if (!$this->hasImplements($node)) {
            return null;
        }
        $this->refactorImplements($node);
        // 2. refactor logout() class method to onLogout()
        $onLogoutSuccessClassMethod = $node->getMethod('onLogoutSuccess');
        if (!$onLogoutSuccessClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $node->stmts[] = $this->onSuccessLogoutClassMethodFactory->createFromOnLogoutSuccessClassMethod($onLogoutSuccessClassMethod);
        // 3. add getSubscribedEvents() class method
        $classConstFetch = $this->nodeFactory->createClassConstReference('Symfony\\Component\\Security\\Http\\Event\\LogoutEvent');
        $eventReferencesToMethodNames = [new \Rector\Symfony\ValueObject\EventReferenceToMethodNameWithPriority($classConstFetch, 'onLogout', 64)];
        $getSubscribedEventsClassMethod = $this->getSubscribedEventsClassMethodFactory->create($eventReferencesToMethodNames);
        $node->stmts[] = $getSubscribedEventsClassMethod;
        $this->removeNode($onLogoutSuccessClassMethod);
        return $node;
    }
    private function refactorImplements(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $class->implements[] = new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface');
        foreach ($class->implements as $key => $implement) {
            if (!$this->isName($implement, $this->successHandlerObjectType->getClassName())) {
                continue;
            }
            unset($class->implements[$key]);
        }
    }
    private function hasImplements(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'Symfony\\Component\\Security\\Http\\Logout\\LogoutSuccessHandlerInterface')) {
                return \true;
            }
        }
        return \false;
    }
}
