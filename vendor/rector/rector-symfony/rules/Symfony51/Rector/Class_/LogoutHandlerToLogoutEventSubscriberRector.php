<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony51\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory;
use Rector\Symfony\NodeFactory\OnLogoutClassMethodFactory;
use Rector\Symfony\ValueObject\EventReferenceToMethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/36243
 *
 * @see \Rector\Symfony\Tests\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector\LogoutHandlerToLogoutEventSubscriberRectorTest
 */
final class LogoutHandlerToLogoutEventSubscriberRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\OnLogoutClassMethodFactory
     */
    private $onLogoutClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory
     */
    private $getSubscribedEventsClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $logoutHandlerObjectType;
    public function __construct(OnLogoutClassMethodFactory $onLogoutClassMethodFactory, GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory, ClassAnalyzer $classAnalyzer)
    {
        $this->onLogoutClassMethodFactory = $onLogoutClassMethodFactory;
        $this->getSubscribedEventsClassMethodFactory = $getSubscribedEventsClassMethodFactory;
        $this->classAnalyzer = $classAnalyzer;
        $this->logoutHandlerObjectType = new ObjectType('Symfony\\Component\\Security\\Http\\Logout\\LogoutHandlerInterface');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change logout handler to an event listener that listens to LogoutEvent', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class SomeLogoutHandler implements LogoutHandlerInterface
{
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class SomeLogoutHandler implements EventSubscriberInterface
{
    public function onLogout(LogoutEvent $logoutEvent): void
    {
        $request = $logoutEvent->getRequest();
        $response = $logoutEvent->getResponse();
        $token = $logoutEvent->getToken();
    }

    /**
     * @return array<string, string[]>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => ['onLogout'],
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, $this->logoutHandlerObjectType)) {
            return null;
        }
        if (!$this->classAnalyzer->hasImplements($node, 'Symfony\\Component\\Security\\Http\\Logout\\LogoutHandlerInterface')) {
            return null;
        }
        foreach ($node->implements as $key => $implement) {
            if ($this->isName($implement, $this->logoutHandlerObjectType->getClassName())) {
                unset($node->implements[$key]);
            }
        }
        $node->implements[] = new FullyQualified('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface');
        // 2. refactor logout() class method to onLogout()
        $logoutClassMethod = $node->getMethod('logout');
        if (!$logoutClassMethod instanceof ClassMethod) {
            return null;
        }
        $node->stmts[] = $this->onLogoutClassMethodFactory->createFromLogoutClassMethod($logoutClassMethod);
        $classMethodStmtKey = $logoutClassMethod->getAttribute(AttributeKey::STMT_KEY);
        unset($node->stmts[$classMethodStmtKey]);
        // 3. add getSubscribedEvents() class method
        $classConstFetch = $this->nodeFactory->createClassConstReference('Symfony\\Component\\Security\\Http\\Event\\LogoutEvent');
        $eventReferencesToMethodNames = [new EventReferenceToMethodName($classConstFetch, 'onLogout')];
        $getSubscribedEventsClassMethod = $this->getSubscribedEventsClassMethodFactory->create($eventReferencesToMethodNames);
        $node->stmts[] = $getSubscribedEventsClassMethod;
        return $node;
    }
}
